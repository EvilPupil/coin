<?php

namespace Coin\command;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use Coin\Coin;
use pocketmine\Player;

class mycoin extends PluginCommand implements PluginIdentifiableCommand{
	public function __construct(Coin $plugin, $cmd = "mycoin"){
		parent::__construct($cmd, $plugin);
		$this->setUsage("/$cmd");
		$this->setDescription("查看自己硬幣數量");
		$this->setPermission("Coin.command.mycoin");
	}

	public function execute(CommandSender $sender, $label ,  array $params){
		if(isset($params[0])){
			$sender->sendMessage("§4 - 請輸入/".$this->getName());
			return false;
		}

		$val = $this->getPlugin()->myCoin($sender);
		$msg = "";

		switch($val){
			case "-3":
				$msg .= "§4 - 你必須進入遊戲使用這個指令.";
				break;
			case "-1":
				$msg .= "§4 - 沒有找到信息 , 請稍後重試.";
				break;
			case "1":
				$v = $this->getPlugin()->getInstance()->coin->get(strtolower($sender->getName()));
				$msg .= "§a - 你的硬幣剩餘".$v.".";
				break;
		}

		$sender->sendMessage($msg);
		return true;
	}
}