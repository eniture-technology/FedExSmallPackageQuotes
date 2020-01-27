<?php

namespace Eniture\FedExSmallPackageQuotes\App;

/**
 * Class State
 * @package Eniture\FedExSmallPackageQuotes\App
 */
class State extends \Magento\Framework\App\State
{
    /**
     *
     */
    public function validateAreaCode()
    {
        if (!isset($this->_areaCode)) {
            $this->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }
    }
}
