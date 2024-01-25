<?
namespace Avtoresurs\Core\Handlers\Crm;

use Avtoresurs\Core\Helper\LeadAssignHelper;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

class LeadHandler
{
	public static function assignLeadToMailQueue($params)
	{
		Loader::includeModule('timeman');
		$techUser = LeadAssignHelper::getTechUser();
		$queue = LeadAssignHelper::getMailQueue();
		if($params['SOURCE_ID'] !== $queue['source']) {
			return;
		}

		$timeNow = strtotime(FormatDate('H:i', time()));
		$timeFrom = strtotime('08:45');
		$timeTo = strtotime('17:30');
		$crmLead = new \CCrmLead(false);
		$arFields = [];

		if($timeNow > $timeFrom && $timeNow < $timeTo) {
			$assignedUserTime = new \CTimeManUser($params['ASSIGNED_BY_ID']);
			if($assignedUserTime->State() != 'OPENED' && count($queue['queue']) > 0) {
				$randUser = $queue['queue'][array_rand($queue['queue'], 1)];
				$arFields['ASSIGNED_BY_ID'] = $randUser;
			}
		} else {
			$arFields['ASSIGNED_BY_ID'] = $techUser ? : $params['ASSIGNED_BY_ID'];
		}
		$crmLead->Update($params['ID'], $arFields);
	}
}