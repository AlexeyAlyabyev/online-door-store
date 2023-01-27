<?php

class ModelExtensionPaymentRBSCredit extends Model
{
    public function install()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rbs_orders` (
                `oc_order_number` int(11) NOT NULL,
                `rbs_bank_amount` int(11) NOT NULL,
                `rbs_order_state` int(11) NOT NULL,
                PRIMARY KEY (`oc_order_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    }

}