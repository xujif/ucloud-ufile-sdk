<?php
namespace Xujif\UcloudUfileSdk;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;

class UfileSdkException extends \Exception {}

class UfileSdk {
	protected $httpClient;
	protected $bucket;
	protected $pub_key;
	protected $sec_key;
	protected $host;
	protected static function auth($bucket, $pub_key, $sec_key) {
		return function (callable $handler) use ($bucket, $pub_key, $sec_key) {
			return function (
				$request,
				array $options
			) use ($handler, $bucket, $pub_key, $sec_key) {
				$path = $request->getUri()->getPath();
				$method = strtoupper($request->getMethod());
				$paramToSign['method'] = $method;
				foreach (['Content-MD5', 'Content-Type', 'Date'] as $headNeedSign) {
					$v = $request->getHeader($headNeedSign);
					$paramToSign[$headNeedSign] = empty($v) ? "" : $v[0];
				}
				$authString = implode("\n", $paramToSign) . "\n";
				//合并UCloud特殊头
				$headers = $request->getHeaders();
				//标准化CanonicalizedUCloudHeaders
				foreach ($headers as $k => $v) {
					$k = strtolower($k);
					if (strncasecmp($k, "x-ucloud-", strlen('x-ucloud-')) !== 0) {
						continue;
					}
					if (is_array($v)) {
						$v = implode(',', $v);
					}
					$authString .= $k . ":" . trim($v, " ") . "\n";
				}
				//合并资源路径
				$authString .= "/" . $bucket . $path;
				$signature = base64_encode(hash_hmac('sha1', $authString, $sec_key, true));
				$authToken = "UCloud " . $pub_key . ":" . $signature;
				$request = $request->withHeader('Authorization', $authToken);
				if (in_array($method, ['POST', 'PUT'])) {
					$request = $request->withHeader('Content-Length', $request->getBody()->getSize());
				}
				return $handler($request, $options);
			};
		};
	}
	public function __construct($bucket, $pub_key, $sec_key, $suffix = '.ufile.ucloud.cn', $https = false, $debug = false) {
		$this->bucket = $bucket;
		$this->pub_key = $pub_key;
		$this->sec_key = $sec_key;
		$this->host = ($https ? 'https://' : 'http://') . $bucket . $suffix;
		$stack = new HandlerStack();
		$stack->setHandler(new CurlHandler());
		$stack->push(static::auth($bucket, $pub_key, $sec_key));
		$this->httpClient = new Client(['base_uri' => $this->host, 'handler' => $stack, 'debug' => $debug]);
	}

	public function put($key_name, $contents, $headers = array()) {
		$resp = $this->httpClient->request('PUT', $key_name, [
			'headers' => $headers,
			'body' => $contents]);
		return [$resp->getBody()->getContents(), $resp->getStatusCode()];
	}
	public function putFile($key_name, $filePath, $headers = array()) {
		$resp = $this->httpClient->request('PUT', $key_name, [
			'headers' => $headers,
			'body' => fopen($filePath, 'r')]);
		return [$resp->getBody()->getContents(), $resp->getStatusCode()];
	}
	public function get($key_name) {
		$resp = $this->httpClient->get($key_name);
		if ($resp->getStatusCode() != 200) {
			throw new UfileSdkException("get $key_name error :" . $resp->getStatusCode());
		}
		return $resp->getBody()->getContents();
	}
	public function exists($key_name) {
		$resp = $this->httpClient->head($key_name);
		return $resp->getStatusCode() == 200;
	}
	public function size($key_name) {
		$resp = $this->httpClient->head($key_name);
		if ($resp->getStatusCode() != 200) {
			throw new UfileSdkException("size $key_name error :" . $resp->getStatusCode());
		}
		return (int) $resp->getHeader('Content-Length')[0];
	}

	public function mime($key_name) {
		$resp = $this->httpClient->head($key_name);
		if ($resp->getStatusCode() != 200) {
			throw new UfileSdkException("size $key_name error :" . $resp->getStatusCode());
		}
		return $resp->getHeader('Content-Type')[0];
	}
	public function delete($key_name) {
		$resp = $this->httpClient->delete($key_name);
		if ($resp->getStatusCode() != 200) {
			throw new UfileSdkException("delete $key_name error :" . $resp->getStatusCode());
		}
		return true;
	}
	public function meta($key_name) {
		$resp = $this->httpClient->head($key_name);
		if ($resp->getStatusCode() != 200) {
			throw new UfileSdkException("size $key_name error :" . $resp->getStatusCode());
		}
		$meta = [];
		foreach ($resp->getHeaders() as $k => $v) {
			$meta[$k] = $v[0];
		}
		return $meta;
	}

}
