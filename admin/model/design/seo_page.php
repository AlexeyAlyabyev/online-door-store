<?php
class ModelDesignSeoPage extends Model {
	public function addSeoPage($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_page` SET query = '" . $this->db->escape($data['query']) . "', keyword = '" . $data['keyword'] . "', full_seo_url = '" . $this->db->escape($data['full_seo_url']) . "', meta_title = '" . $this->db->escape(trim($data['meta_title'])) . "', meta_description = '" . $this->db->escape(trim($data['meta_description'])) . "', h1 = '" . $this->db->escape(trim($data['h1'])) . "', description = '" . $this->db->escape(trim($data['description'])) . "', date_modified = NOW()");
	}

	public function editSeoPage($seo_page_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_page` SET query = '" . $this->db->escape($data['query']) . "', keyword = '" . $data['keyword'] . "', full_seo_url = '" . $this->db->escape($data['full_seo_url']) . "', meta_title = '" . $this->db->escape(trim($data['meta_title'])) . "', meta_description = '" . $this->db->escape(trim($data['meta_description'])) . "', h1 = '" . $this->db->escape(trim($data['h1'])) . "', description = '" . $this->db->escape(trim($data['description'])) . "', date_modified = NOW() WHERE seo_page_id = '" . $seo_page_id . "'");
	}

	public function deleteSeoPage($seo_page_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_page` WHERE seo_page_id = '" . $seo_page_id . "'");
	}

	public function getSeoPageByFullUrl($full_seo_url) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_page` WHERE full_seo_url = '" . $this->db->escape($full_seo_url) . "'");

		return $query->row;
	}	

	public function getPathByCategory($category_id) {
		$category_id = (int)$category_id;
		if ($category_id < 1) return false;

		static $path = null;
		if (!isset($path)) {
			$path = $this->cache->get('category.seopath');
			if (!isset($path)) $path = array();
		}

		if (!isset($path[$category_id])) {
			$max_level = 10;

			$sql = "SELECT CONCAT_WS('_'";
			for ($i = $max_level-1; $i >= 0; --$i) {
				$sql .= ",t$i.category_id";
			}
			$sql .= ") AS path FROM " . DB_PREFIX . "category t0";
			for ($i = 1; $i < $max_level; ++$i) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "category t$i ON (t$i.category_id = t" . ($i-1) . ".parent_id)";
			}
			$sql .= " WHERE t0.category_id = '" . $category_id . "'";

			$query = $this->db->query($sql);

			$path[$category_id] = $query->num_rows ? $query->row['path'] : false;

			$this->cache->set('category.seopath', $path);
		}

		return $path[$category_id];
	}
}