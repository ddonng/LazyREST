<?php
/*
$kv = new SaeKV();
$kv->init();
$kv->set( 'aa' , 'ddonng' );


$a['public']=1;
$a['on']=0;
$kv->set( 'aa' , serialize($a));
echo serialize($a).'   ';
echo $kv->get( 'aa').'   ';
print_r($a);
*/

$requesturl='http://cqdd.sinaapp.com/api/odp_nation/insert/nation_name=214';

$ch=curl_init($requesturl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$cexecute=curl_exec($ch);
curl_close($ch);

$result = json_decode($cexecute,true);
print_r($result);
//echo $result['data'][0]->nation_id;
echo $result['err_msg'];
echo "<br>nation_name: ".$result['data'][0]['nation_name'];
