#ucloud ufile sdk
base on https://docs.ucloud.cn/api-docs/ufile-api/ 
#usage
    $sdk = new UfileSdk('storage','ap_pub_key','ap_pub_secret');
    $sdk->put('text.txt',"content");
    $sdk->putFile('text.txt',"/path/to/yourfile");
    $contents = $sdk->get('dd');
    $exists =  $sdk->exists('dd222');
    $size = $sdk->delete('dd');
或者直接查看tests/sdkTest.php
