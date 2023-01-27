<?php

class ModelExtensionFeedFastSitemap extends Model {
  public function getProducts($args) {
    $query = $this->db->query("SELECT p.product_id, p.image, DATE_FORMAT(GREATEST(p.date_added, p.date_modified),'%Y-%m-%dT%TZ') as date_modified, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . $args['language_id'] . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . $args['store_id'] . "' ORDER BY p.sort_order, LCASE(pd.name)");
    
    return $query->rows;
  }  
  
  public function getCategories($args) {
    $query = $this->db->query("SELECT c.category_id, DATE_FORMAT(GREATEST(c.date_added, c.date_modified),'%Y-%m-%dT%TZ') as date_modified FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.status = '1' AND cd.language_id = '" . $args['language_id'] . "' AND c2s.store_id = '" . $args['store_id'] . "' ORDER BY c.sort_order, LCASE(cd.name)"
    );

    return $query->rows;
  }
  
  public function getManufacturers($args) {
    $query = $this->db->query("SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) AND m2s.store_id = '" . $args['store_id'] . "' ORDER BY m.sort_order, m.name"); 
    
    return $query->rows;
  }  
  
  public function getInformations($args){
		$query = $this->db->query("SELECT i.information_id FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE id.language_id = '" . $args['language_id'] . "' AND i2s.store_id = '" . $args['store_id'] . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;  
  } 
  
  public function getNews($args){
    $query = $this->db->query("SELECT n.news_id FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . $args['language_id'] . "' AND n.status = '1' ORDER BY date_added DESC");   
    
    return $query->rows; 
  }

	public function getSeoFilterPages(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_page ORDER BY date_modified DESC");   
    
    return $query->rows; 
	}
}
