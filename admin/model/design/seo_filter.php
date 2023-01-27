<?php
class ModelDesignSeoFilter extends Model {
	public function addSeoFilter($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$data['store_id'] . "', language_id = '" . (int)$data['language_id'] . "', query = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function editSeoFilter($seo_url_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$data['store_id'] . "', language_id = '" . (int)$data['language_id'] . "', query = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "' WHERE seo_url_id = '" . (int)$seo_url_id . "'");
	}

	public function deleteSeoFilter($seo_url_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE seo_url_id = '" . (int)$seo_url_id . "'");
	}

	public function getSeoFiltersByKeyword($keyword) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE keyword = '" . $this->db->escape($keyword) . "'");

		return $query->rows;
	}	
	
	public function getSeoFilterByQuery($query) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE store_id='".(int)$this->config->get('config_store_id')."' AND language_id = '".(int)$this->config->get('config_language_id')."' AND `query` = '" . $query . "'");
		
		return $query->row;
	}	

	public function getSeoFilterAttributes($data = array()) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute_group_description`");

		$data = array();

		foreach($query->rows as $attribute_group) {

			$attr_group_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute` a LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON a.attribute_id = ad.attribute_id WHERE a.attribute_group_id = " . $attribute_group['attribute_group_id'] . " ORDER BY ad.name");
			
			$data[] = array(
				'name' 				=> $attribute_group['name'],
				'attributes' 	=> $attr_group_query->rows,
			);
		}

		return $data;
	}

	public function getSeoFilterAttributeValues($attribute_id) {
		$query = $this->db->query("SELECT `attribute_id`, `language_id`, `text` FROM `" . DB_PREFIX . "product_attribute` WHERE attribute_id = " . $attribute_id . " GROUP BY text");

		return $query->rows;
	}

	public function getAttributeName($attribute_id) {
		$query = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "attribute_description` WHERE attribute_id = " . $attribute_id);
		return $query->row['name'];
	}

	public function getSeoFilterOptions(){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_description` ORDER BY `name` ASC");
		return $query->rows;
	}

	public function getOptionName($option_id) {
		$query = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "option_description` WHERE option_id = " . $option_id);
		return $query->row['name'];
	}

	public function getSeoFilterOptionValues($option_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value_description` WHERE option_id = " . $option_id);
		return $query->rows;
	}

	public function getSeoFilterManufacturers(){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer` ORDER BY `name` ASC");
		return $query->rows;
	}

	public function getActiveSeoFilterAttributes(){
		$query = $this->db->query("SELECT SUBSTRING(REPLACE(`query`, 'rdrf[attr][', ''), 1, INSTR(REPLACE(`query`, 'rdrf[attr][', ''), ']') - 1) as attribute_id FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE 'rdrf[attr][%' GROUP BY attribute_id");
		return $query->rows;
	}

	public function getActiveSeoFilterOptions(){
		$query = $this->db->query("SELECT SUBSTRING(REPLACE(`query`, 'rdrf[opt][', ''), 1, INSTR(REPLACE(`query`, 'rdrf[opt][', ''), ']') - 1) as option_id FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE 'rdrf[opt][%' GROUP BY option_id");
		return $query->rows;
	}

	public function countSeoFilterManufacturers(){
		$query = $this->db->query("SELECT COUNT(*) as seo_manufacturers_filter FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE 'rdrf[man][%'");
		return $query->row['seo_manufacturers_filter'];
	}

	public function getSeoFilterAttributeActiveValues($attribute_id){
		$query = $this->db->query("SELECT SUBSTRING(`query`, INSTR(`query`,'=') + 1, LENGTH(`query`)) as result FROM `" . DB_PREFIX . "seo_url` WHERE SUBSTRING(REPLACE(`query`, 'rdrf[attr][', ''), 1, INSTR(REPLACE(`query`, 'rdrf[attr][', ''), ']') - 1) = " . $attribute_id);
		
		$result = array();

		foreach ($query->rows as $row){
			$result[] = $row['result'];
		}
		
		return $result;
	}

	public function getSeoFilterOptionActiveValues($option_id){
		$query = $this->db->query("SELECT SUBSTRING(`query`, INSTR(`query`,'=') + 1, LENGTH(`query`)) as result FROM `" . DB_PREFIX . "seo_url` WHERE SUBSTRING(REPLACE(`query`, 'rdrf[opt][', ''), 1, INSTR(REPLACE(`query`, 'rdrf[opt][', ''), ']') - 1) = " . $option_id);
		
		$result = array();
		foreach ($query->rows as $row){
			$result[] = $row['result'];
		}
		
		return $result;
	}

	public function getSeoFilterManufacturersActiveValues(){
		$query = $this->db->query("SELECT SUBSTRING(`query`, INSTR(`query`,'=') + 1, LENGTH(`query`)) as result FROM `oc_seo_url` WHERE SUBSTRING(REPLACE(`query`, 'rdrf[', ''), 1, INSTR(REPLACE(`query`, 'rdrf[', ''), ']') - 1) = 'man'");
		
		$result = array();

		foreach ($query->rows as $row){
			$result[] = $row['result'];
		}
		
		return $result;
	}
}