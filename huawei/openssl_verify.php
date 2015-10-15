<?php

$oriContent = file_get_contents('php://input');
if (null === $oriContent || "" === $oriContent)
{
    echo "{\"result\":1}";
    return;
}

$elements = split('&', $oriContent);
$valueMap = array();
foreach ($elements as $element)
{
    $single = split('=', $element);
    $valueMap[$single[0]] = $single[1];
}

//下面三个if 做的事 是 把sign, extReserved , sysReserved 三个字段的值 进行urlencode
if(null !== $valueMap["sign"])
{
    $valueMap["sign"] = urldecode($valueMap["sign"]);
}
if(null !== $valueMap["extReserved"])
{
    $valueMap["extReserved"]= urldecode($valueMap["extReserved"]);
}
if(null !== $valueMap["sysReserved"])
{
    $valueMap["sysReserved"] = urldecode($valueMap["sysReserved"]);
}

//按照键名对数组排序
ksort($valueMap);
$sign = $valueMap["sign"];

if(empty($sign))
{
    echo "{\"result\":1}";
    return;
}

$content = "";
$i = 0;
//将除了sign之外的 参数拼接成一个字符串，用来后序验证,  结果如： a=123&b=343&c=321
foreach($valueMap as $key=>$value)
{
    if($key != "sign" )
    {
       $content .= ($i == 0 ? '' : '&').$key.'='.$value;
    }
    $i++;
}

//支付公钥
$filename = dirname(__FILE__)."/huaweiSDK/payPublicKey.pem";

if(!file_exists($filename))
{
    echo "{\"result\" : 1 }";
    return;
}
$pubKey = @file_get_contents($filename);
$openssl_public_key = @openssl_get_publickey($pubKey);

$ok = @openssl_verify($content,base64_decode($sign), $openssl_public_key);
@openssl_free_key($openssl_public_key);

$result = "";

if($ok)
{
    #签名验证成功
    $result = "0";
}
else
{
    #签名验证失败
    $result = "1";
}
$res = "{ \"result\": $result} ";
echo $res;
?>
