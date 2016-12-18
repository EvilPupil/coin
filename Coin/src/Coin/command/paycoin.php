<?php

namespace Coin\command;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use Coin\Coin;
use pocketmine\Player;

class paycoin extends PluginCommand implements PluginIdentifiableCommand{
	public function __construct(Coin $plugin, $cmd = "paycoin"){
		parent::__construct($cmd, $plugin);
		$this->setUsage("/$cmd <名字> <數量>");
		$this->setDescription("支付玩家Coin");
		$this->setPermission("Coin.command.paycoin");
	}

	public function execute(CommandSender $sender, $label ,  array $params){
		if(!isset($params[0]) or !isset($params[1]) or isset($params[2])){
			$sender->sendMessage("§4 - 請輸入/".$this->getName()." <名字> <數量>");
			return false;
		}

		$val = $this->getPlugin()->payCoin($sender , $params[0] , $params[1]);
		$msg = "";

		switch($val){
			case "-3":
				$msg .= "§4 - 你必須進入遊戲使用這個指令.";
				break;
			case "-2":
				$msg .= "§4 - 你的硬幣數量不足 , 支付失敗.";
				break;
			case "-1":
				$msg .= "§4 - 找不到這個玩家 , 請檢查是否輸入正確.";
				break;
			case "0":
				$msg .= "§4 - 數量不符合規定(必須是一個§b大於(or equal)0§4的整數).";
				break;
			case "1":
				$msg .= "§a - 成功支付給玩家§b".$params[0]."§a硬幣§b".$params[1]."§a.";
				break;
		}

		$sender->sendMessage($msg);
		return true;
	}
}