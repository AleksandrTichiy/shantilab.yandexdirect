<?php

use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) return;

if ($exception = $APPLICATION->GetException()){
    echo CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_UNINST_ERR'),
        'DETAILS' => $exception->GetString(),
        'HTML' => true
    ]);
}else{
    CAdminMessage::ShowNote(Loc::getMessage('MOD_UNINST_OK'));
}
?>
<form action="<?= $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="submit" name="" value="<?=Loc::getMessage('MOD_BACK')?>">
</form>
