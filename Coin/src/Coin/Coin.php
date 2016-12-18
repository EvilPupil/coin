<?php

namespace Coin;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Utils;

use Coin\command\setcoin;
use Coin\command\addcoin;
use Coin\command\reducecoin;
use Coin\command\paycoin;
use Coin\command\mycoin;

class Coin extends PluginBase implements Listener{

	public static $obj;

	/**
	 * @var  標準錯誤返回類型2
	 */
	const ERROR_2 = -3;
	/**
	 * @var  標準錯誤返回類型1
	 */
	const ERROR_1 = -2;

	/**
	 * @var  有值未找到或丟失返回類型
	 */
	const NOT_FOUND = -1;

	/**
	 * @var  無效的參數或變量返回類型
	 */
	const INVALID = 0;

	/**
	 * @var 函數成功返回類型
	 */
	const SUCCESS = 1;

	public function onEnable(){
		$this->getLogger()->info("插件正在加載...");

		

		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder().'API/');
		//@mkdir($this->getDataFolder().'Card/');

		$this->coin = new Config($this->getDataFolder().'API/coin.yml' , Config::YAML);

		$this->config = new Config($this->getDataFolder().'API/config.yml' , Config::YAML , array(
			"defaultCoin" => 0,
		));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$commandMap = $this->getServer()->getCommandMap();

		$commandMap->register("Coin" , new setcoin($this , "setcoin"));
		$commandMap->register("Coin" , new addcoin($this , "addcoin"));
		$commandMap->register("Coin" , new reducecoin($this , "reducecoin"));
		$commandMap->register("Coin" , new paycoin($this , "paycoin"));
		$commandMap->register("Coin" , new mycoin($this , "mycoin"));

		$this->testVersion();
	}

	public function onDisable(){
		$this->getLogger()->info("插件正在卸載...");
	}

	public function onLoad(){
		if(!self::$obj instanceof Coin){
			self::$obj = $this;
		}
	}

	/**
	 * [接口函數 : 通過Coin::getInstance()->x 調用Coin類的屬性 or 方法]
	 * @return [this] [本類(Coin)的$this]
	 */
	public static function getInstance(){
		return self::$obj;
	}

	/**
	 * [玩家加入遊戲觸發函數]
	 * @param  PlayerJoinEvent $event [class]
	 * @return [null]                 [沒有返回值]
	 */
	public function onJoin(PlayerJoinEvent $event){
		$player = strtolower($event->getPlayer()->getName());

		if(!$this->coin->exists($player)){
			$this->coin->set($player , (int)$this->config->get("defaultCoin"));
			$this->saveConfig();
		}
	}

	/**
	 * [設置一個玩家硬幣數量]
	 * @param  [string] $player [設置硬幣的目標玩家名字]
	 * @param  [int]    $number [設置硬幣的數量]
	 * @return [int]            [返回各種判斷]
	 */
	public function setCoin($player , $number){	
		$lp = strtolower($player);
		
		if($number < 0 or !is_numeric($number)){
			return self::INVALID;
		}

		if(!$this->coin->exists($lp)){
			return self::NOT_FOUND;
		}

		$this->coin->set($lp ,  (int)$number);
		$this->saveConfig();
		return self::SUCCESS;
	}
	
	/**
	 * [增加一個玩家的硬幣數量]
	 * @param  [string] $player [增加硬幣的目標玩家名字]
	 * @param  [int]    $number [增加硬幣的數量]
	 * @return [int]            [返回各種判斷]
	 */
	public function addCoin($player , $number){		
		$lp = strtolower($player);
		
		if($number < 0 or !is_numeric($number)){
			return self::INVALID;
		}
		
		if(!$this->coin->exists($lp)){
			return self::NOT_FOUND;
		}
		
		$this->coin->set($lp , (int)($this->coin->get($lp) + $number));
		$this->saveConfig();
		return self::SUCCESS;
	}

	/**
	 * [減少一個玩家硬幣數量]
	 * @param  [string] $player [減少硬幣的目標玩家名字]
	 * @param  [int]    $number [減少硬幣的數量]
	 * @return [int]            [返回各種判斷]
	 */
	public function reduceCoin($player , $number){
		$lp = strtolower($player);

		if($number < 0 or !is_numeric($number)){
			return self::INVALID;
		}
		
		if(!$this->coin->exists($lp)){
			return self::NOT_FOUND;
		}

		if($this->coin->get($lp) < $number){
			$this->coin->set($lp , 0);
			$this->saveConfig();
			return self::ERROR_1;
		}

		$this->coin->set($lp , (int)($this->coin->get($lp) - $number));
		$this->saveConfig();
		return self::SUCCESS;
	}

	/**
	 * [支付玩家硬幣]
	 * @param  [string] $sender [發送指令玩家名字]
	 * @param  [string] $player [目標玩家名字]
	 * @param  [int]    $number [支付數量]
	 * @return [int]            [返回各種判斷]
	 */
	public function payCoin($sender , $player , $number){
		$ls = strtolower($sender->getName());
		$lp = strtolower($player);

		if(!($sender instanceof Player)){
			return self::ERROR_2;
		}

		if($number < 0 or !is_numeric($number)){
			return self::INVALID;
		}

		if(!$this->coin->exists($lp)){
			return self::NOT_FOUND;
		}

		if($this->coin->get($lp) < $number){
			return self::ERROR_1;
		}

		$this->coin->set($lp , (int)($this->coin->get($lp) + $number));
		$this->coin->set($ls , (int)($this->coin->get($ls) - $number));
		$this->saveConfig();
		return self::SUCCESS;
	}

	/**
	 * [查看自己硬幣數量]
	 * @param  [string] $sender [發送指令玩家名字]
	 * @return [int]            [返回各種判斷]
	 */
	public function myCoin($sender){
		$ls = strtolower($sender->getName());

		if(!($sender instanceof Player)){
			return self::ERROR_2;
		}

		if(!$this->coin->exists($ls)){
			return self::NOT_FOUND;
		}

		return self::SUCCESS;
	}

	public function saveConfig()
	{
		$this->coin->save();
	}

	private function testVersion(){
		try{
			    $this->getLogger()->info("Checking for updates... It may be take some while.");

				$desc = yaml_parse(Utils::getURL("https://raw.githubusercontent.com/Undefinedmes/coin/master/Coin/plugin.yml"));
				
				$description = $this->getDescription();
				if(version_compare($description->getVersion(), $desc["version"]) < 0){
					$this->getLogger()->warning("Coin 插件檢測到有新的更新 , 請更新后使用");
					$this->getServer()->shutdown();
				}else{
					$this->getLogger()->notice("Coin 插件沒有發現更新");
				}
			}catch(\Exception $e){
				$this->getLogger()->warning("檢測版本發生異常");
			}	
	}

	/*public function createConfig_card(){
		$this->card = (new Config($this->getDataFolder().'Card/card.yml' , Config::YAML))->getAll();
		if(is_array($this->card) == false){
			$this->card = array();
		}
	}

	public function saveConfig_card(){
		$cardData = new Config($this->getDataFolder().'Card/card.yml' , Config::YAML);
		$cardData->setAll($this->card);
		$cardData->save();
	}*/
}

