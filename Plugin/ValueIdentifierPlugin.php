<?php
/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Webscale\CacheExclusions\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Serialize\Serializer\Json;

class ValueIdentifierPlugin
{
    /**
     * XML path to enable the plugin
     */
    const XML_PATH_ENABLE = 'cache_exclusions/general/enable';

    /**
     * XML path to dynamic rows
     */
    const XML_PATH_DYNAMIC_ROWS = 'cache_exclusions/configuration/dynamic_rows';

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param RequestHttp $request
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Json|null $serializer
     */
    public function __construct(
        RequestHttp          $request,
        Context              $context,
        ScopeConfigInterface $scopeConfig,
        Json                 $serializer = null
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param Identifier $subject
     * @param string $result
     * @return string
     */
    public function afterGetValue(Identifier $subject, string $result): string
    {
        if($this->scopeConfig->getValue(self::XML_PATH_ENABLE) == 0) {
            return $result;
        }
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(ResponseHttp::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];
        $dynamicRows = $this->scopeConfig->getValue(self::XML_PATH_DYNAMIC_ROWS);

        foreach ($this->serializer->unserialize($dynamicRows) as $value) {
            $search = $value['parameter'];
            $data[1] = $this->removeqsvar($data[1], $search);
        }

        return sha1($this->serializer->serialize($data));
    }

    /**
     * @param string $url
     * @param string $varname
     * @return string
     */
    public function removeqsvar($url, $varname)
    {
        list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
        parse_str($qspart, $qsvars);
        unset($qsvars[$varname]);
        $newqs = http_build_query($qsvars);
        return $urlpart . '?' . $newqs;
    }
}
