<?php
class ControllerProductCategory extends Controller {
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
			$data['next_page'] = "false";
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		// Проверка на спам
		$data['spam_check'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		// Проверка на спам

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);
			
			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);
				if ($category_info) {
					// Если мы в дочерней категории, получаем название и id основной на первой итерации foreach
					if (count($data['breadcrumbs']) < 2) {
						$data["main_category_name"] = $category_info["name"];
						$data["main_category_id"] = $path_id;
					}
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		// Если был вход в админку, показывает на странице кноку редактирования
		if (isset($this->session->data['user_token'])) {
			$data["edit"] = "admin/index.php?route=catalog/category/edit&" . "user_token=" .  $this->session->data['user_token'] . "&category_id=" . $category_id;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {

			// Last-Modified
			$template_name = "product/category";
			$template_path = DIR_TEMPLATE . $this->config->get('theme_default_directory') . "/template/"  . $template_name . ".twig";
			$LastModified_unix = max(strtotime($category_info['date_modified']) - 10800, filectime($template_path), filectime(__FILE__));
			$LastModified = gmdate("D, d M Y H:i:s", $LastModified_unix) . " GMT";

			$ifModified = false;
			$ifModified = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '', 5));

			if ($ifModified && $ifModified >= $LastModified_unix) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
				exit;
			}
			$this->response->addHeader('Last-Modified: '. $LastModified);
			// ---------------

			// Передаем id и имя корневой категории
			if (!isset($data["main_category_name"]) || !isset($data["main_category_id"])) {
				$data["main_category_name"] = $category_info['name'];
				$data["main_category_id"] = $category_info['category_id'];
			}

			// ----------------------------------		CUSTOM CODE START		--------------------------
			// Берем из БД данные по товару с самой низкой ценой и цепляем эту цену в шаблон для баннера

			$lowest_category_price = $this->model_catalog_product->getProducts(array(
				'filter_category_id' => $category_id,
				'sort'               => 'p.price',
				'order'              => 'ASC',
				'limit'              => 1
			));

			$lowest_category_price = array_shift($lowest_category_price);

			if (isset($lowest_category_price["special"]))
				$data['lowest_category_price'] = round($lowest_category_price['special']);
			else
				$data['lowest_category_price'] = round($lowest_category_price['price']);

			$data['mobile'] = false;
			// Определение мобильного устройства
			$mobile_agents = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
			$agent = strtolower($_SERVER['HTTP_USER_AGENT']);    
			foreach ($mobile_agents as $value) {    
					if (strpos($agent, $value) !== false) {
						$data['mobile'] = "true";
						break;
					}
			}
			// ----------------------------------		CUSTOM CODE END		--------------------------

			$data['banner_text'] = $category_info['banner_text'];
			$data['banner_title'] = $category_info['banner_title'];

			// Подставляем Город в текст описания при переходе с рекламных ссылок и подтягиваем его из пар-ра location
			if (isset($_GET['location'])){
				$category_info['description'] = str_replace("Москве и области", $_GET["location"], $category_info['description']);
				$category_info['description'] = str_replace("Москве", $_GET["location"], $category_info['description']);

				$category_info['meta_title'] = str_replace("Москве и области", $_GET["location"], $category_info['meta_title']);
				$category_info['meta_title'] = str_replace("Москве", $_GET["location"], $category_info['meta_title']);

				$category_info['meta_description'] = str_replace("Москве и области", $_GET["location"], $category_info['meta_description']);
				$category_info['meta_description'] = str_replace("Москве", $_GET["location"], $category_info['meta_description']);

				$data['heading_title'] = $category_info['name']." в ".$_GET["location"];
			}
			elseif (isset($_GET["category"])) 
				$data['heading_title'] = $_GET["category"];
			elseif (isset($category_info['h1']) && $category_info['h1'] != '') 
				$data['heading_title'] = $category_info['h1'];
			else 
				$data['heading_title'] = $category_info['name'];
			// Конец

			$seo_filter_page = $this->model_catalog_category->getSeoPageByFullUrl(explode("?", $this->request->server['REQUEST_URI'])[0]);

			if ($seo_filter_page) {
				$category_info['meta_title'] = $seo_filter_page['meta_title'];
				$category_info['meta_description'] = $seo_filter_page['meta_description'];
				$data['heading_title'] = $seo_filter_page['h1'];
			}

			if (isset($_GET['page']))  {
				$category_info['meta_title'] .= " | Страница ".$_GET['page'];
				$category_info['meta_description'] .= " | Страница ".$_GET['page'];
				$data['heading_title'] .= " | Страница ".$_GET['page'];
			}

			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			// $this->document->setKeywords($category_info['meta_keyword']);				

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if ($category_info['image']) {
				$data['thumb'] = '/image/' . $category_info['image'];
			} 

			if (!$seo_filter_page)
				$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			else
				$data['description'] = html_entity_decode($seo_filter_page['description'], ENT_QUOTES, 'UTF-8');

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = array();
			$data['tag_categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);
			
			foreach ($results as $result) {
				if ($result['parent_id'] == $category_id) {
					if ($result['tag']) {
						$data['tag_categories'][] = array(
							'name' => $result['name'],
							'sort' => $result['sort_order'],
							'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
						);
					} elseif ($result['top']) {
						$data['categories'][] = array(
							'name' => $result['name'],
							'sort' => $result['sort_order'],
							'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
						);
					}
				}
			}
			// Сортировка массива категорий по значению, указанному в админке
			usort($data['categories'], function ($a, $b) {
				return $a['sort'] <=> $b['sort'];
			});
			usort($data['tag_categories'], function ($a, $b) {
				return $a['sort'] <=> $b['sort'];
			});

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {

				if ($result['stock_status_id'] == 5) continue;

				// Получаем из аттрибутов цвет двери
				$result['product_color'] = "";

				if (!$data['mobile'] ) {
					$attribute_groups = $this->model_catalog_product->getProductAttributes($result['product_id']);

					if (!empty($attribute_groups)) {
						foreach ($attribute_groups[0]['attribute'] as $attribute_group){
							if ($attribute_group['attribute_id'] == 12) $result['product_color'] = $attribute_group['text'];
							if ($attribute_group['attribute_id'] == 51) $result['product_color'] = $attribute_group['text'];
						}
					}
				}

				// Подгружаем сжатое изображение картинки в общий список каталога
				if ($result['compressed_image'] && is_file(DIR_IMAGE . $result['compressed_image'])){
					if (strpos($result['compressed_image'], ".webp") !== false || strpos($result['compressed_image'], ".avif") !== false)
						$image = 'image/' . $result['compressed_image'];		
					else
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));			
				} elseif ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					if (strpos($result['image'], ".webp") !== false || strpos($result['image'], ".avif") !== false)
						$image = "/image/".$result['image'];
					else
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = "/image/placeholder.svg";
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				// РАЗБИВАЕМ ЦЕНУ НА РАЗРЯДЫ
				$price = str_replace(preg_replace("/[^0-9]/", '', $price), number_format((float)preg_replace("/[^0-9]/", '', $price), 0, '', ' '), $price);

				if ($special) {
					$special = str_replace(preg_replace("/[^0-9]/", '', $special), number_format(preg_replace("/[^0-9]/", '', $special), 0, '', ' '), $special);
					$special_label = round((1 - $result['special'] / $result['price']) * 100);
				}
				else 
					$special_label = "";

				$words_to_replace = array("Межкомнатная дверь ", "Входная дверь ", "Серая", "Белая", "Межкомнатная ", "дверь ");
				$result['name'] = str_replace($words_to_replace, "", $result['name']);
				$result['name'] = ucfirst($result['name']);

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'price'       => $price,
					'special'     => $special,
					'product_color'=> $result['product_color'],
					'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url),
					'special_label' => $special_label,
					'reserve_img'  => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'))
				);
			}
						
			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => 'Дешевле',
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url),
				'img'		=> 'image/category/low-price.svg'
			);

			$data['sorts'][] = array(
				'text'  => 'Дороже',
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url),
				'img'		=> 'image/category/hight-price.svg'
			);		

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			if (isset($this->request->get['rdrf'])) {
				foreach ($this->request->get['rdrf'] as $key => $filter_param){
					if (is_array($filter_param)) {
						foreach ($filter_param as	$lower_key => $param){
							if (is_array($param))
								foreach ($param as $index => $value){
									$url .= '&rdrf['.$key.']['.$lower_key.']['.$index.']=' . $value;
								}
							else
								$url .= '&rdrf['.$key.']['.$lower_key.']=' . $param;
						}
					}
					else {
						$url .= '&rdrf['.$key.']=' . $filter_param;
					}
				}
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$num_pages = ceil($product_total / $limit);
			if ($page < $num_pages) $this->document->addLink(str_replace('{page}', $page + 1, $pagination->url), 'next');
			if ($page > 1 && $page <= $num_pages) $this->document->addLink(str_replace('{page}', $page - 1, $pagination->url), 'prev');
			
			// Смена окончания в слове "товаров" в зависимости от числа
			$plural = $product_total % 10;

			if ($product_total > 10 && $product_total < 21 || !$plural || $plural > 4) 
				$data['results'] = $product_total . " товаров";
			elseif ($plural > 1 && $plural < 5) 
				$data['results'] = $product_total . " товара";
			else
				$data['results'] = $product_total . " товар";
			
			if (!$seo_filter_page)
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
			else
				$this->document->addLink($this->request->server['REQUEST_SCHEME'] . "://" . $this->request->server['SERVER_NAME'] . $seo_filter_page['full_seo_url'], 'canonical');

			$url = $_SERVER["REQUEST_URI"];
			$link_array = explode('/',$url);
			array_pop($link_array);

			
			$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js', 'footer');
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			if (end($link_array) == "invisible") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/interior-door/video/invisible.mp4" type="video/mp4"></video>'.$data['content_top'];
			if (end($link_array) == "kupe-system") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/interior-door/video/kupe.mp4" type="video/mp4"></video>'.$data['content_top'];
			if (end($link_array) == "compack") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/interior-door/video/compack.mp4" type="video/mp4"></video>'.$data['content_top'];
			if (end($link_array) == "knizhka") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/interior-door/video/book.mp4" type="video/mp4"></video>'.$data['content_top'];
			if (end($link_array) == "povorotnaya-sistema") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/interior-door/video/roto.mp4" type="video/mp4"></video>'.$data['content_top'];
			if (end($link_array) == "magic") $data['content_top'] = '<video autoplay="" muted="" loop=""><source src="catalog/view/theme/custom/image/doors-systems/magic_system.mp4" type="video/mp4"></video>'.$data['content_top'];

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
