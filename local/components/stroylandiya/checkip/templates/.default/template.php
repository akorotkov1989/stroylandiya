<?php
if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) die();

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

Extension::load('ui.dialogs.messagebox');
Extension::load('ui.buttons');
Extension::load('ui.forms');
Extension::load("ui.bootstrap4");
?>

<div class="b-ipchecker">
    <form name="ipchecker" class="f-ipcheck" id="ipchecker" method="post">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <input type="text" id="ip-address" class="ui-ctl-element form-control" placeholder="<?= Loc::getMessage('STROYLANDIYA_ENTER_IP')?>">
                </div>
                <div class="col-lg-3">
                    <button class="ui-btn ui-btn-sm"><?= Loc::getMessage('STROYLANDIYA_SEARCH_IP')?></button>
                </div>
            </div>
        </div>
    </form>

    <div class="container">
        <div class="row">
            <div class="b-ipinfo col-lg-12">
                <?= Loc::getMessage('STROYLANDIYA_IP_INFO')?>
            </div>
        </div>
    </div>
</div>


<script>
    BX.message({
        STROYLANDIYA_NOT_VALID_IP: '<?= Loc::getMessage("STROYLANDIYA_NOT_VALID_IP")?>',
        STROYLANDIYA_UNDEFINED_ERROR_MESSAGE: '<?= Loc::getMessage("OFFICEMAG_ENTER_DISCOUNT_CODE")?>',
    });
</script>