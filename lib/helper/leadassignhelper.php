<?
namespace Avtoresurs\Core\Helper;

use Bitrix\Crm\LeadTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Loader::includeModule('mail');
Loader::includeModule('crm');
Loader::includeModule('timeman');
Loader::includeModule('avtoresurs.core');

class LeadAssignHelper
{
	public static function getMailQueue(): array
	{
		$result = [];
		$mailBoxId = Option::get('avtoresurs.core', 'AVTORESURS_EMAIL_BOX');
		if($mailBox = MailboxTable::getList([
			'filter' => [
				'=ID' => $mailBoxId
			],
			'select' => ['OPTIONS']
		])->fetch()) {
			$usersQueue = [];
			$users = $mailBox['OPTIONS']['crm_lead_resp'];
			foreach ($users as $user) {
				if((new \CTimeManUser($user))->State() === 'OPENED') {
					$usersQueue[] = $user;
				}
			}
			$result = [
				'queue' => $usersQueue,
				'source' => $mailBox['OPTIONS']['crm_lead_source']
			];
		}
		return $result;
	}

	public static function getTechUser(): int
	{
		$usersTech = Option::get('avtoresurs.core', 'AVTORESURS_TECH_USER');
		return $usersTech ? : 0;
	}

	public function assignedLeads()
	{
		$leads = LeadTable::getList([
			'filter' => [
				'=ASSIGNED_BY_ID' => self::getTechUser(),
				'=OPENED' => 'Y'
			],
			'select' => ['ID']
		]);
		$queue = self::getMailQueue();
		$crmLead = new \CCrmLead(false);
		while($lead = $leads->fetch()) {
			$randUser = $queue['queue'][array_rand($queue['queue'], 1)];
			$arFields = ['ASSIGNED_BY_ID' => $randUser];
			$crmLead->Update($lead['ID'], $arFields);
		}
	}

}