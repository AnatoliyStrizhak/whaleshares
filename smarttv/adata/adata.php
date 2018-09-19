<?php
require __DIR__ . '/vendor/autoload.php';

use GrapheneNodeClient\Commands\CommandQueryData;
use GrapheneNodeClient\Commands\Commands;
use GrapheneNodeClient\Commands\Single\GetAccountsCommand;
use GrapheneNodeClient\Connectors\WebSocket\SteemitWSConnector;


//Set params for query
$commandQuery = new CommandQueryData();

$data = [['0' => 'astrizak',]];
$commandQuery->setParams($data);

$command = new GetAccountsCommand(new SteemitWSConnector());
$res = $command->execute(
    $commandQuery
);


$val=$res['result'][0]["reputation"];

if($val > 0)
{
    $rep = (max(log10($val) - 9, 0) * 9 + 25); 
}
else
{
    $rep=max(log10(-$val) - 9, 0) * -9 + 25;
}

$vests=round($res['result'][0]["vesting_shares"],3);
$pwr=round(($res['result'][0]["voting_power"]));
$pwr=($pwr+($pwr*0.02))/100;


echo "document.getElementById('wls').innerHTML='".$res['result'][0]["balance"]."';";
echo "document.getElementById('vests').innerHTML='WHALESTAKE: ".$vests."';";
echo "document.getElementById('vpwr').innerHTML='".$pwr."%';";
echo "document.getElementById('informer').innerHTML='';";
echo "document.getElementById('dt').innerHTML='".date("d.m.Y")."';";


$color="green";

if($pwr<=80)
{
    $color="yellow";
}
else if($pwr<60)
{
    $color="red";
}

$slider='<div style="width:'.$pwr.'%; height:18px; background-color:'.$color.';"></div>';

echo "document.getElementById('slider').innerHTML='".$slider."';";


?>