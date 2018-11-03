<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/28 2:40
 */


namespace luweiss\Curl;


class Curl
{
    public $ch;

    public $baseUrl;
    public $url;

    public $timeout = 5;
    public $autoRedirect = true;
    public $maxRedirect = 5;
    public $redirectCount = 0;
    public $sslVerifyPeer = false;
    public $sslVerifyHost = false;

    public $response;
    public $curlErrno;
    public $curlError;

    public function __construct()
    {
        $this->response = new CurlResponse();
    }

    /**
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @param integer $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param boolean $autoRedirect
     * @return $this
     */
    public function setAutoRedirect($autoRedirect)
    {
        $this->autoRedirect = $autoRedirect;
        return $this;
    }

    /**
     * @param integer $maxRedirect
     * @return $this
     */
    public function setMaxRedirect($maxRedirect)
    {
        $this->maxRedirect = $maxRedirect;
        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @return CurlResponse
     * @throws CurlException
     */
    public function get($url, $params = [])
    {
        $this->url = $this->getUrl($url, $params);
        return $this
            ->init()
            ->setCommonOptions()
            ->exec();
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $params
     * @return CurlResponse
     * @throws CurlException
     */
    public function post($url, $data = [], $params = [])
    {
        $this->url = $this->getUrl($url, $params);
        if (function_exists('curl_file_create') && (is_array($data) || $data instanceof \ArrayObject)) {
            foreach ($data as $k => $v) {
                if (strpos($v, '@') === 0) {
                    $data[$k] = curl_file_create(trim($v, '@'));
                }
            }
        }
        return $this
            ->init()
            ->setCommonOptions()
            ->setOption(CURLOPT_POST, true)
            ->setOption(CURLOPT_POSTFIELDS, $data)
            ->exec();
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws CurlException
     */
    private function getUrl($url, $params = [])
    {
        return CurlHelper::appendQueryString(
            CurlHelper::appendBaseUrl($this->baseUrl, $url),
            CurlHelper::paramsToQueryString($params)
        );
    }

    /**
     * @return $this
     */
    private function init()
    {
        $this->ch = curl_init($this->url);
        return $this;
    }

    /**
     * @param $option
     * @param $value
     * @return $this
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
        return $this;
    }

    /**
     * @return $this
     */
    private function setCommonOptions()
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        // https请求时要设置为false 不验证证书和hosts  FALSE 禁止 cURL 验证对等证书（peer's certificate）
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
        // 检查服务器SSL证书中是否存在一个公用名(common name)。
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);
        return $this;
    }

    /**
     * @return CurlResponse
     */
    private function exec()
    {
        $this->response->body = curl_exec($this->ch);
        $this->response->headers = curl_getinfo($this->ch);
        if ($this->autoRedirect
            && $this->redirectCount < $this->maxRedirect
            && $this->response->headers['http_code']
            && in_array($this->response->headers['http_code'], [301, 302])
            && $this->response->headers['redirect_url']
        ) {
            $this->redirectCount++;
            $this->setOption(CURLOPT_URL, $this->response->headers['redirect_url']);
            return $this->exec();
        }
        $this->curlErrno = curl_errno($this->ch);
        $this->curlError = curl_error($this->ch);
        curl_close($this->ch);
        return $this->response;
    }
}
