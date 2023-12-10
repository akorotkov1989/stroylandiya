<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Проверка IP адреса');
?>

<?php
$APPLICATION->IncludeComponent(
    'stroylandiya:checkip',
    '',
    [],
    false
);
?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');