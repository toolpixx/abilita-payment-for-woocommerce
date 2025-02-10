<?php declare(strict_types=1);

namespace abilita\payment\services;

use abilita\payment\services\WC_Abilita_Helper;

defined('ABSPATH') || exit;

class WC_Abilita_VatId_Service
{
    private $vatId;

    public function __construct() { }

    public function setVatId($vatId)
    {
        $this->vatId = strtoupper($vatId);
        $this->vatId = preg_replace('/ /', '', $this->vatId);
    }

    public function validate()
    {
        $pattern = '/^([A-Z]{2})(\d{8,12}[A-Z]?)$/';
        if (preg_match($pattern, $this->vatId)) {
            return true;
        }

        return false;
    }

    /**
     * @description never use yet
     * @return bool
     */
    public function validateViesApi()
    {
        $client = new \SoapClient('https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
        $params = [
            'countryCode' => substr($this->vatId, 0, 2),
            'vatNumber'   => substr($this->vatId, 2)
        ];

        try {
            $result = $client->checkVat($params);
            return (bool) $result->valid;
        } catch (\Exception $e) {
            return true;
        } catch (\SoapFault $e) {
            return true;
        }
    }

    public function validateChecksum() {

        $check_digits = substr($this->vatId, 2, 8);
        $check_digit  = substr($this->vatId, -1);

        $product = 10;
        for ($i = 0; $i < 8; $i++) {
            $sum = ($check_digits[$i] + $product) % 10;
            if ($sum == 0) {
                $sum = 10;
            }
            $product = ($sum * 2) % 11;
        }
        $calculated_check_digit = 11 - $product;
        if ($calculated_check_digit == 10) {
            $calculated_check_digit = 0;
        }

        return $check_digit == $calculated_check_digit;
    }
}