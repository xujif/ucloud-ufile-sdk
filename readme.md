# ucloud ufile sdk
> forked from  [xujif/ucloud-ufile-sdk](https://github.com/xujif/ucloud-ufile-sdk)

## usage
```
    $sdk = new UfileSdk('storage','api_pub_key','api_pub_secret');
    $sdk->put('text.txt',"content");
    $sdk->putFile('text.txt',"/path/to/yourfile");
    $contents = $sdk->get('dd');
    $exists =  $sdk->exists('dd222');
    $size = $sdk->delete('dd');
```
或者直接查看tests/sdkTest.php

## modify
1. 开启了put()的header参数
2. put()、putFile()增加响应码校验
3. 优化delete()的响应判断
