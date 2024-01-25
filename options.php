<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('AVTORESURS_MODULE_NAME', 'avtoresurs.core');

use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages(__FILE__);
Loader::includeModule('mail');

$emailList = [];
$mailBox = MailboxTable::getList(['filter' => ['=ACTIVE' => 'Y'], 'select' => ['ID', 'NAME']]);
while($mail = $mailBox->fetch()) {
    $emailList[$mail['ID']] = $mail['NAME'];
}

function renderUsersList($inputName, $inputValue)
{
	global $APPLICATION;
	ob_start();

	$APPLICATION->IncludeComponent(
		'bitrix:main.user.selector',
		'',
		[
			'INPUT_NAME' => $inputName,
			'LIST' => $inputValue ? [$inputValue] : [],
			'ID' => 'mod-user-selector-' . Random::getString('10'),
			'AVAILABLE_USER_LIST' => [],
			'SELECTOR_OPTIONS' => array(
				'enableAll' => 'N',
                'allowUserSearch' => 'Y',
				'enableDepartments' => 'Y',
                'multiple' => 'N',
				'lastTabDisable' => false,
				'userSearchArea' => 'I'
			)
		],
		null,
		array('HIDE_ICONS' => 'Y')
	);

	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

$tabs = [
	[
		'DIV' => 'general',
		'TAB' => 'Основные настройки',
		'TITLE' => 'Основные настройки'
	]
];

$options = [
	'general' => [
		[
			'AVTORESURS_EMAIL_BOX',
			Loc::getMessage('AVTORESURS_OPTIONS_EMAIL_BOX'),
			'',
			['selectbox', $emailList]
		],
		[
			'AVTORESURS_TECH_USER',
			Loc::getMessage('AVTORESURS_OPTIONS_TECH_USER'),
			renderUsersList('AVTORESURS_TECH_USER', 0),
			['statichtml']
		],
	]
];

$save = $request->isPost() && ($request->get('save') || $request->get('apply'));

if (check_bitrix_sessid() && strlen($_POST['save']) > 0) {
	foreach ($options as $option) {
        if($option[1][0] == 'AVTORESURS_TECH_USER') {
	        $optionValue = $request->getPost($option[1][0]);
            Option::set(AVTORESURS_MODULE_NAME, $option[1][0], $optionValue);
        } else {
	        __AdmSettingsSaveOptions(AVTORESURS_MODULE_NAME, $option);
        }
	}
	LocalRedirect($APPLICATION->GetCurPageParam());
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->begin();
?>

<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode(AVTORESURS_MODULE_NAME), LANGUAGE_ID)?>">
	<? $tabControl->BeginNextTab(); ?>
    <?foreach ($options['general'] as $option):?>
        <?if($option[0] == 'AVTORESURS_TECH_USER'):?>
            <?$techUserVal = Option::get(AVTORESURS_MODULE_NAME, 'AVTORESURS_TECH_USER');?>
            <tr>
                <td class="adm-detail-valign-middle adm-detail-content-cell-l" width="50%">
                    <?=Loc::getMessage('AVTORESURS_OPTIONS_TECH_USER')?>
                </td>
                <td class="adm-detail-valign-middle adm-detail-content-cell-l" width="50%">
                    <div style="text-align: left">
					    <?=renderUsersList('AVTORESURS_TECH_USER', $techUserVal)?>
                    </div>
                </td>
            </tr>
        <?else:?>
            <? __AdmSettingsDrawRow(AVTORESURS_MODULE_NAME, $option); ?>
        <?endif;?>
    <?endforeach;?>


	<? $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false)); ?>
	<?= bitrix_sessid_post(); ?>
	<? $tabControl->End(); ?>
</form>
