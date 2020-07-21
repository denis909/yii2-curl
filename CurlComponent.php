<?php

namespace denis909\yii;

use Exception;
use yii\helpers\ArrayHelper;

class CurlComponent extends \yii\base\Component
{

    public $result;

    public $sslVerifyPeer = false;

    public $sslVerifyHost = false;

    public $followLocation = true;

    public function query($url, $post = null, array $options = [])
    {
        if ($post !== null)
        {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $post;
        }

        $ch = curl_init();

        curl_setopt_array($ch, ArrayHelper::merge([
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->sslVerifyPeer,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerifyHost,
            CURLOPT_FOLLOWLOCATION => $this->followLocation
        ], $options));

        $this->result = curl_exec($ch);

        if ($this->result === false)
        {
            $error = curl_error($ch);

            curl_close($ch);

            throw new Exception($error);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code !== 200)
        {
            throw new Exception('HTTP Code: ' . $code);
        }

        curl_close($ch);

        return $this->result;
    }

    public function jsonQuery($url, $post = null, array $options = [])
    {
        if (!array_key_exists(CURLOPT_HTTPHEADER, $options))
        {
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json', 'accept: application/json'];
        }

        $content = $this->query($url, $post, $options);

        return json_decode($content, true);
    }

    public function imageQuery($url, $post = null, array $options = [])
    {
        $content = $this->query($url, $post, $options);

        if (!$content || !($size = getimagesizefromstring($content)))
        {
            throw new Exception('File "'  . $url . '" is not an image.');
        }

        return $content;
    }

}