<?php
class ControllerExtensionFeedFastYandexSitemap extends Controller {
	public function index() {    
		$log = new Log('fast_yandex_sitemap.log');  
		$feed =  (version_compare(VERSION, '3.0', '>=')) ? 'feed_' : '';
		if ($this->config->get($feed.'fast_sitemap_status')) {    
			$this->load->model('extension/feed/fast_sitemap');
			$this->load->model('tool/image');  
			$args = array(
				'language_id' => (int)$this->config->get('config_language_id'),
				'store_id' => (int)$this->config->get('config_store_id')
			);
			
			$start = microtime(true);    
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
			$output .= "\n";

			$categories = $this->model_extension_feed_fast_sitemap->getCategories($args);  
			if ($categories) {
				foreach ($categories as $category) {
					$output .= "<url>\n";
					$output .= "  <loc>" . $this->url->link('product/category', 'path=' . $category['category_id']) . "</loc>\n";  
					$output .= "  <lastmod>" . $category['date_modified'] . "</lastmod>\n";
					$output .= "  <changefreq>daily</changefreq>\n";
					$output .= "  <priority>1.0</priority>\n";
					$output .= "</url>\n";
				}
			}

			$manufacturers = $this->model_extension_feed_fast_sitemap->getManufacturers($args);   
			if ($manufacturers) {
				foreach ($manufacturers as $manufacturer) {
					$output .= "<url>\n";
					$output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . "</loc>\n";
					$output .= "  <changefreq>daily</changefreq>\n";
					$output .= "  <priority>0.9</priority>\n";
					$output .= "</url>\n";
				}
			}

			$seo_filter_pages = $this->model_extension_feed_fast_sitemap->getSeoFilterPages();
			if ($seo_filter_pages) {
				foreach ($seo_filter_pages as $seo_filter_page) {
					$output .= "<url>\n";
					$output .= "  <loc>" . $this->request->server['REQUEST_SCHEME'] . "://" . $this->request->server['SERVER_NAME'] . $seo_filter_page['full_seo_url'] . "</loc>\n";  
					$output .= "  <lastmod>" . $seo_filter_page['date_modified'] . "</lastmod>\n";
					$output .= "  <changefreq>daily</changefreq>\n";
					$output .= "  <priority>0.9</priority>\n";
					$output .= "</url>\n";
				}
			}

			$informations = $this->model_extension_feed_fast_sitemap->getInformations($args); 
			if ($informations) {
				foreach ($informations as $information) {
					$output .= "<url>\n";
					$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . "</loc>\n";
					$output .= "  <changefreq>weekly</changefreq>\n";
					$output .= "  <priority>0.8</priority>\n";
					$output .= "</url>\n";
				}       
			}   

			$products = $this->model_extension_feed_fast_sitemap->getProducts($args);    
			if ($products) {
				foreach ($products as $product) {
					$output .= "<url>\n";
					$output .= "  <loc>" . $this->url->link('product/product', 'product_id=' . $product['product_id']) . "</loc>\n";
					$output .= "  <lastmod>" . $product['date_modified'] . "</lastmod>\n";
					$output .= "  <changefreq>daily</changefreq>\n";
					$output .= "  <priority>0.7</priority>\n";
					$output .= "</url>\n";
				}
			}

			if ($this->config->get('news')) { 
				$news = $this->model_extension_feed_fast_sitemap->getNews($args);   
				if ($news) {
					foreach ($news as $new) {
						$output .= "<url>\n";
						$output .= '  <loc>' . $this->url->link('information/news', 'news_id=' . $new['news_id']) . "</loc>\n";
						$output .= "  <changefreq>weekly</changefreq>\n";
						$output .= "  <priority>0.6</priority>\n";
						$output .= "</url>\n";
					}
				}
			}
			$output .= "</urlset>\n";

			$time = microtime(true) - $start;
			$log->write(sprintf('Sitemap was generated for %.4F s. ', $time).'Request from '.$_SERVER["REMOTE_ADDR"].' '.$_SERVER['HTTP_USER_AGENT']);
			
			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);       
		}
	}

}
