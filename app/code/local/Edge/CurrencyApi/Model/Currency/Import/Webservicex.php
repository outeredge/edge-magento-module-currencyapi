<?php
/**
 * Currency rate import model (From www.webservicex.net)
 *
 * @category   Edge
 * @package    Edge_CurrencyApi
 * @author     outer/edge team <hello@outeredgeuk.com>
 */
class Edge_CurrencyApi_Model_Currency_Import_Webservicex extends Mage_Directory_Model_Currency_Import_Webservicex
{
    protected $_url = 'https://currency-api.appspot.com/api/{{CURRENCY_FROM}}/{{CURRENCY_TO}}.xml';

    protected function _convert($currencyFrom, $currencyTo, $retry=0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            $response = $this->_httpClient
                ->setUri($url)
                ->setConfig(array('timeout' => Mage::getStoreConfig('currency/webservicex/timeout')))
                ->request('GET')
                ->getBody();

            $xml = simplexml_load_string($response, null, LIBXML_NOERROR);
            if( !$xml ) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
                return null;
            }
            return (float) $xml->rate;
        }
        catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
            }
        }
    }
}
