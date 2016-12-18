<?php

namespace Coin\command;

use pocketmine\command\PluginCommand;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;

use Coin\Coin;
use pocketmine\Player;

class addcoin extends PluginCommand implements PluginIdentifiableCommand{
	public function __construct(Coin $Plugin , $cmd = "addcoin"){
		parent::__construct($cmd , $Plugin);
		$this->setUsage("/$cmd <名字> <數量>");
		$this->setDescription("為玩家添加Coin");
		$this->setPermission("Coin.command.addcoin");
	}
	
	public function execute(CommandSender $sender , $label , array $params){
		if(!isset($params[0]) or !isset($params[1]) or isset($params[2])){
			$sender->sendMessage("§4 - 請輸入/".$this->getName()." <名字> <數量>");
			return false;
		}
		
		$val = $this->getPlugin()->addCoin($params[0] , $params[1]);
		$msg = "";

		switch($val){
			case "-1":
				$msg .= "§4 - 找不到這個玩家 , 請檢查是否輸入正確.";
				break;
			case "0":
				$msg .= "§4 - 數量不符合規定(必須是一個§b大於(or equal)0§4的整數).";
				break;
			case "1":
				$msg .= "§a - 成功為玩家§b".$params[0]."§a添加硬幣數量§b".$params[1]."§a.";
				break;
		}

		$sender->sendMessage($msg);
		return true;
	}
	
}

