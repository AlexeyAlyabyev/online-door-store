<?php
class Pagination {
	public $total = 0;
	public $page = 1;
	public $limit = 5;
	public $num_links = 5;
	public $url = '';
	public $text_first = '';
	public $text_last = '';
	public $text_next = '<i class="fa fa-angle-right"></i>';
	public $text_prev = '<i class="fa fa-angle-left"></i>';

	public function render() {
		$total = $this->total;

		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}

		if (!(int)$this->limit) {
			$limit = 10;
		} else {
			$limit = $this->limit;
		}

		$num_links = $this->num_links;
		$num_pages = ceil($total / $limit);

		$this->url = str_replace('%7Bpage%7D', '{page}', $this->url);

		$output = '<ul class="pagination">';

    if ($page > 1) { //Если номер текущей страницы больше 1
 
      if ($page - 1 === 1) { //Если при этом предыдущая страница, относительно текущей, имеет номер 1
        $output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $this->text_prev . '</a></li>'; //Выводится кнопка "назад" с ссылкой на 1ю страницу
      } else {
        $output .= '<li><a href="' . str_replace('{page}', $page - 1, $this->url) . '">' . $this->text_prev . '</a></li>'; //Выводится кнопка "назад" с ссылкой на предыдущую страницу
      }
      
      if( $page >= ($num_links - 1) ){ //Если текущая страница больше или равна количеству выводимых ссылок - 1 (в данном случае >=5)
        $output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), "", $this->url) . '">' . '1' . '</a></li>'; //Выводим ссылку на первую страницу
        $output .= '<li class="dots"><span>' . '...' . '</span></li>'; //Выводим многоточие
      }
   
    }

		if ($num_pages > 1) {
			if ($num_pages <= $num_links) {
				$start = 1;
				$end = $num_pages;
			} else {
				$start = $page - floor($num_links / 2);
				$end = $page + floor($num_links / 2);

				if ($start < 1) {
					$end += abs($start) + 1;
					$start = 1;
				}

				if ($end > $num_pages) {
					$start -= ($end - $num_pages);
					$end = $num_pages;
				}
			}

			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= '<li class="active"><span>' . $i . '</span></li>';
				} else {
					if ($i === 1) {
						$output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $i . '</a></li>';
					} else {
						$output .= '<li><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>';
					}
				}
			}
		}

//		if ($page < $num_pages) {
//			$output .= '<li><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>';
//			$output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $this->text_last . '</a></li>';
//		}
      if ($page < $num_pages) { // Если номер текущей страницы меньше, чем всего страниц
        if ( ($num_pages - $num_links + 1) < $page){ // Если (всего страниц - количество выводимых ссылок + 1) меньше, чем номер текущей страницы
          $output .= '<li><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>'; //Выводим кнопку "далее"
        }
        else {
          $output .= '<li class="dots"><span>' . '...' . '</span></a></li>'; //Выводим многоточие
          $output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $num_pages . '</a></li>'; //Выводим ссылку на последнюю страницу
          $output .= '<li><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>'; //Выводим кнопку "далее"
        }
      }

		$output .= '</ul>';

		if ($num_pages > 1) {
			return $output;
		} else {
			return '';
		}
	}
}
