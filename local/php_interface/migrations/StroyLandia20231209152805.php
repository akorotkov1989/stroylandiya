<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;

class StroyLandia20231209152805 extends Version
{
    protected $description = "Миграция на создание HiLoad блока для ранения IP адресов";

    protected $moduleVersion = "4.6.1";

    private HelperManager $helper;

    const HLBLOCK_CODE = 'geoip';
    const TABLE_NAME = 'geoip';

    public function __construct()
    {
        $this->helper = $this->getHelperManager();
    }

    public function up(): void
    {
        // Создаем Hblock и поля для него
        $hlblockId = $this->helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::HLBLOCK_CODE,
            'TABLE_NAME' => self::TABLE_NAME,
            'LANG' => [
                'ru' => [
                    'NAME' => 'Справочник IP адресов',
                ],
                'en' => [
                    'NAME' => 'IP address directory',
                ],
           ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $this->helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_IP', [
            'FIELD_NAME' => 'UF_IP',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'E',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'en' => '',
                'ru' => 'IP адрес',
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => '',
                'ru' => '',
            ],
            'LIST_FILTER_LABEL' => [
                'en' => '',
                'ru' => '',
            ],
            'ERROR_MESSAGE' => [
                'en' => '',
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'en' => '',
                'ru' => '',
            ],
        ]);

        $this->helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_IP_INFO', [
            'FIELD_NAME' => 'UF_IP_INFO',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'E',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'en' => '',
                'ru' => 'Информация об IP адресе',
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => '',
                'ru' => '',
            ],
            'LIST_FILTER_LABEL' => [
                'en' => '',
                'ru' => '',
            ],
            'ERROR_MESSAGE' => [
                'en' => '',
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'en' => '',
                'ru' => '',
            ],
        ]);

        // Меняем тип поля с TEXT на CHAR
        $connect = Application::getConnection();
        $connect->query('ALTER TABLE ' .  self::TABLE_NAME . ' MODIFY UF_IP CHAR(15);');
        // Создаем индекс для поля UF_IP
        $connect->query('CREATE INDEX UF_IP_INDEX ON ' .  self::TABLE_NAME . ' (UF_IP);');
    }

    public function down(): void
    {
        $this->helper->Hlblock()->deleteHlblockIfExists(self::HLBLOCK_CODE);
    }
}
