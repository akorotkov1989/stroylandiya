<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Web\HttpClient;

/**
 * Компонент для проверки IP адреса
 */
class CheckIpAddressComponent extends CBitrixComponent implements Controllerable
{
    /**
     * Коллекция ошибок
     * @var ErrorCollection
     */
    protected ErrorCollection $errorCollection;

    /**
     * Класс для доступа к ORM HiLoad блока IP адресов
     * @var string
     */
    protected string $hlEntityClass;

    /**
     * Email, куда будут приходить сообщения об ошибках
     */
    const ERROR_REPORT_EMAIL = 'test@example.com';

    /**
     * Код HL блока
     */
    const HL_BLOCK_CODE = 'Geoip';

    /**
     * URL API сервиса для получения IP
     */
    const API_SERVICE_URL = 'http://api.ipstack.com/';

    /**
     * Ключ доступа
     */
    const API_KEY = 'fd06e6d04210b21d848f1dcbcaf7cb31';

    /**
     * @param null $component
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        Loader::includeModule('highloadblock');
        $this->setParams();
    }

    /**
     * Установка параметров
     * @throws \Bitrix\Main\SystemException
     */
    protected function setParams()
    {
        define('ERROR_EMAIL', self::ERROR_REPORT_EMAIL);
        $this->errorCollection = new ErrorCollection();
        $this->hlEntityClass = HighloadBlockTable::compileEntity(self::HL_BLOCK_CODE)->getDataClass();
    }


    /**
     * Конфигурируем ajax методы
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'getIpInfo' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * Получение информации по IP адресу
     * @return AjaxJson
     */
    public function getIpInfoAction(): AjaxJson
    {
        try {
            $request = Application::getInstance()->getContext()->getRequest();
            $ipAddress = $request->getPost('ipAddress');

            if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                throw new Exception(Loc::getMessage('STROYLANDIYA_NO_VALID_IP'));
            }

            $ipJson = $this->getIpInfoInHl($ipAddress);
            if (!$ipJson) {
                $ipJson = $this->getIpInfoByApi($ipAddress);

                if (!$ipJson) {
                    throw new Exception(Loc::getMessage('STROYLANDIYA_NO_INFORMATION_IP'));
                }
            }

            json_decode($ipJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(Loc::getMessage('STROYLANDIYA_NO_VALID_JSON'));
            }

            $result = AjaxJson::createSuccess(['message' => $ipJson]);;
        } catch (Exception $exception) {
            $this->errorCollection->setError(new Error($exception->getMessage()));
            $result = AjaxJson::createError($this->errorCollection);

            // Отправка сообщений об ошибках на Email
            $errors = [];
            foreach ($this->errorCollection->toArray() as $error) {
                $errors = $error->getMessage();
            }
            SendError(implode(PHP_EOL, $errors));
        }

        return $result;
    }

    /**
     * Получение информации по IP адресу из HiLoad блока
     * @param string $ipAddress
     * @return string|null
     */
    protected function getIpInfoInHl(string $ipAddress): ?string
    {
        return $this->hlEntityClass::getList([
            'select' => ['UF_IP_INFO'],
            'filter' => ['=UF_IP' => $ipAddress],
            'limit'  => 1
        ])->fetch()['UF_IP_INFO'];
    }

    /**
     * Получение информации по IP адресу через API
     * @param string $ipAddress
     * @return string|null
     * @throws Exception
     */
    protected function getIpInfoByApi(string $ipAddress): ?string
    {
        $httpClient = new HttpClient();
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $url = self::API_SERVICE_URL . $ipAddress . '?access_key=' . self::API_KEY;
        $httpClient->query('GET', $url);

        switch ($httpClient->getStatus()) {
            case 200:
                $result = $httpClient->getResult();
                $responseArray = json_decode($result, true);
                if (!empty($responseArray['error']) &&  $responseArray['success'] == false) {
                    throw new Exception($responseArray['error']['info']);
                }
                $this->addNewIpToHl($ipAddress, $result);
                break;
            default:
                throw new Exception(Loc::getMessage('STROYLANDIYA_API_ERROR'));
        }

        return $result;
    }

    /**
     * Добавление нового IP адреса в HiLoad блок
     * @param string $ipAddress
     * @param string $result
     * @throws Exception
     */
    protected function addNewIpToHl(string $ipAddress, string $result): void
    {
        $result = $this->hlEntityClass::add([
            'UF_IP' => $ipAddress,
            'UF_IP_INFO' => $result
        ]);

        if (!$result->isSuccess()) {
            throw new Exception(implode(PHP_EOL, $result->getErrorMessages()));
        }
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}