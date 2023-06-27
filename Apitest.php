<?php
class ControllerInformationApitest extends Controller {
	private $error = array();


    function SendTelegramBotShvetS( $title, $name_client, $email_client, $message_client ) {
        /*** описание метода api telegram ***/
        /*** https://core.telegram.org/bots/api#sendmessage ***/

        $tg_user = '585302880'; // id пользователя, которому отправиться сообщения
        $bot_token = '5034226533:AAHwcbes4hyszdGryZA-u7Iz0EG5QARhUas'; // токен бота
        date_default_timezone_set("Europe/Kiev");
        $full_today_date_time = date("d.m.Y H:i:s");

        /***
        $type :
        1 - Форма обратной связи "Главная"
        2 - Форма обратной связи "Popup"
        3 - Форма обратной связи "Contact Page"
        4 - Опрос
        5 - Подписка
         ***/
        $message_for_telegram = '';
        $message_for_telegram = "" . $title . " ( " . $full_today_date_time . " ) \n\nName: " . $name_client . " \nEmail: " . $email_client . " \nMessage: \n" . $message_client;

        /*** параметры, которые отправятся в api телеграмм ***/
        $params = array(
            'chat_id' => $tg_user, // id получателя сообщения
            'text' => $message_for_telegram, // текст сообщения
            'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $bot_token . '/sendMessage'); // адрес api телеграмм
        curl_setopt($curl, CURLOPT_POST, true); // отправка данных методом POST
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // максимальное время выполнения запроса
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // параметры запроса
        $result = curl_exec($curl); // запрос к api
        curl_close($curl);

        //$this->response->addHeader('Content-Type: application/json');
        //$this->response->setOutput(json_encode($result));
    }

	public function index() {

        $this->load->language('product/search');

        $this->load->language('common/header');

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $this->document->setRobots('noindex,follow');

        require(DIR_SYSTEM . '/library/phpQuery/phpQuery.php');

        $data['breadcrumbs']  = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $url = '';

        if (isset($this->request->get['search'])) {
            $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('product/search', $url)
        );

        if (isset($this->request->get['search'])) {
            $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->request->get['search']);
        } elseif (isset($this->request->get['tag'])) {
            $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);
        } else {
            $this->document->setTitle($this->language->get('heading_title'));
        }

        if (isset($this->request->get['search'])) {
            $data['heading_title'] = $this->language->get('heading_title') . ' - ' . $this->request->get['search'];
        } else {
            $data['heading_title'] = $this->language->get('heading_title');
        }

        if (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        } else {
            $search = '';
        }


        $data['bool_search'] = false;
        if (!empty($search)) {
            $data['bool_search'] = true;

            /*$current_count = $this->model_catalog_product->getCountSearch(1);
            $new_counter = $current_count + 1;
            $this->model_catalog_product->updateCountSearch(1, (int)$new_counter);*/

            /*** Записываем запросы от клиентов в БД ***/
            /*$client_ip = '';
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $client_ip = $_SERVER['REMOTE_ADDR'];
            }
            $this->model_catalog_product->addQueryInfo($search, $client_ip);*/

            $search_str = '';
            $arrayParseRUTOR = $this->ParseRUTOR($search);
            $arrayParseKINOZAL = $this->ParseKINOZAL($search);

            $resultsArrayParse = array_merge($arrayParseRUTOR, $arrayParseKINOZAL);


            usort($resultsArrayParse, function ($a, $b) {
                $date_a = strtotime(str_replace('.', '-', $a['date_publish']));
                $date_b = strtotime(str_replace('.', '-', $b['date_publish']));
                return $date_b - $date_a;
            });

            foreach ($resultsArrayParse as $key => $value) {
                if ($value['distribute'] == 0) {
                    unset($resultsArrayParse[$key]);
                }
            }

            $data['resultsArrayParse'] = $resultsArrayParse;
            $data['count_result'] = count($resultsArrayParse);
        }


        $data['search'] = $search;


        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/apitest', $data));
	}




    public function ParseKINOZAL($search_str)
    {
        //header('Content-Type: text/html; charset=utf-8');
        //$search_str = 'Тарас Шевченко';
        //$search_str = 'Пацаны';

        // Адрес прокси-сервера
        $proxy = 'proxy.example.com:8080';
        // Создаем новый объект cURL
        $ch = curl_init();

        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'Content-Type: UTF-8'
        );

        // Устанавливаем параметры запроса
        curl_setopt($ch, CURLOPT_URL, 'https://kinozal.tv/browse.php?s=' . $search_str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Выполняем запрос и получаем ответ
        $response = curl_exec($ch);
        // Закрываем соединение cURL
        curl_close($ch);


        // Получаем HTML-код страницы результатов поиска
        $url = 'https://kinozal.tv/browse.php?s=' . $search_str;
        //$html = file_get_contents($url);

        $html = $response;

        //$str = iconv('cp1251', 'utf-8', iconv('utf-8', 'cp1252', $html));


        // Создаем объект phpQuery и загружаем HTML-код страницы результатов поиска
        $doc = phpQuery::newDocument($html);

        // Получаем список элементов с заголовками торрентов на странице
        //$results = $doc->find('.gai a');
        //$results = $doc->find('.gai');


        $arrayTorrents = array();
        $count_elem = 0;

        $rows = $doc->find('.bx2_0 table tr');
        $count_rows = 1;
        foreach ($rows as $row) {
            if ($count_rows != 1) {
                // Обходим ячейки строки
                $cells = pq($row)->find('td');
                $count_elem_str = 1;
                foreach ($cells as $cell) {
                    //echo pq($cell)->html() . "<br>";
                    if ($count_elem_str == 1) {
                        //это нам не надо
                    }
                    if ($count_elem_str == 2) {
                        $name = pq($cell)->text();
                        $link_url = pq($cell)->find('a')->eq(0)->attr('href');

                        $arrayTorrents[$count_elem]['name'] = $name;
                        $arrayTorrents[$count_elem]['download'] = '';
                        $arrayTorrents[$count_elem]['magnet'] = '';
                        $arrayTorrents[$count_elem]['url'] = 'https://kinozal.tv' . $link_url;

                        // Выводим содержимое ячейки
                        //echo pq($cell)->text() . "<br>";
                        //echo pq($cell)->html() . "<br>";

                        //echo '#1 (DOWNLOAD): ' . $link_download . "<br>";
                        //echo '#2 (MAGNET): ' . $link_magnet . "<br>";
                    }
                    if ($count_elem_str == 3) {
                        $count_comment = pq($cell)->text();
                        $arrayTorrents[$count_elem]['count_comment'] = $count_comment;
                    }
                    if ($count_elem_str == 4) {
                        $size = pq($cell)->text();
                        if (strpos($size, 'ГБ') !== false) {
                            $size = str_replace('ГБ', 'GB', $size);
                        }
                        if (strpos($size, 'МБ') !== false) {
                            $size = str_replace('МБ', 'MB', $size);
                        }
                        $arrayTorrents[$count_elem]['size'] = $size;
                    }
                    if ($count_elem_str == 5) {
                        $pirs_up = pq($cell)->text();
                    }
                    if ($count_elem_str == 6) {
                        $pirs_down = pq($cell)->text();

                        $arrayTorrents[$count_elem]['distribute'] = $pirs_up;
                        $arrayTorrents[$count_elem]['pumping'] = $pirs_down;

                        $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $pirs_up . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $pirs_down . '</span>';

                        $arrayTorrents[$count_elem]['pirs'] = $pirs_html;
                    }

                    if ($count_elem_str == 7) {
                        $date_string = trim(pq($cell)->text());
                        $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                        $data_date = explode(" ", $date_string);

                        $new_date = $data_date[0] . '-' . $data_date[1] . '-' . $data_date[2];

                        //$date_create = DateTime::createFromFormat('d-m-y', $new_date);
                        //$date_formatted = $date_create->format('d-m-Y');
                        //$date = $date_formatted;

                        $date = date("d-m-Y", strtotime($new_date));
                        $arrayTorrents[$count_elem]['date_publish'] = $date;
                    }
                    if ($count_elem_str == 8) {
                        //это нам не надо
                    }
                    $count_elem_str++;
                }
                $count_elem++;
                //echo "<br>";
            }
            $count_rows++;

        }
        //var_dump($arrayTorrents);


        // Получаем ссылку на следующую страницу результатов поиска, если она есть
        //$nextPageLink = $doc->find('.pager a:contains("След.")')->attr('href');
        //$nextPageLink = $doc->find('#index b a')->end()->attr('href');

        /**** НАДО ДОДЕЛАТЬ ПЕРЕХОДЫ *****/
        $pages = $doc->find('.paginator li', 0);
        $count_page = 0;
        if (count($pages) > 1) {
            $count_page = count($pages) - 1;
        }
        //&g=0&page=1
        //'https://kinozal.tv/browse.php?s=' . $search_str
        if ($count_page > 0) {
            for ($i = 1; $i <= $count_page; $i++) {
                // Создаем новый объект cURL
                $ch = curl_init();

                $headers = array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                    'Content-Type: UTF-8'
                );

                // Устанавливаем параметры запроса
                curl_setopt($ch, CURLOPT_URL, 'https://kinozal.tv/browse.php?s=' . $search_str . '&g=0&page=' . $i);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($ch, CURLOPT_PROXY, $proxy);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Выполняем запрос и получаем ответ
                $response = curl_exec($ch);
                // Закрываем соединение cURL
                curl_close($ch);
                // Получаем HTML-код страницы результатов поиска
                $url = 'https://kinozal.tv/browse.php?s=' . $search_str;
                //$html = file_get_contents($url);
                $html = $response;
                //$str = iconv('cp1251', 'utf-8', iconv('utf-8', 'cp1252', $html));
                // Создаем объект phpQuery и загружаем HTML-код страницы результатов поиска
                $doc = phpQuery::newDocument($html);

                // Получаем список элементов с заголовками торрентов на странице
                //$results = $doc->find('.gai a');
                //$results = $doc->find('.gai');

                $arrayTorrentsNEXT = array();
                $count_elem = 0;

                $rows = $doc->find('.bx2_0 table tr');
                $count_rows = 1;
                foreach ($rows as $row) {
                    if ($count_rows != 1) {
                        // Обходим ячейки строки
                        $cells = pq($row)->find('td');
                        $count_elem_str = 1;
                        foreach ($cells as $cell) {
                            //echo pq($cell)->html() . "<br>";
                            if ($count_elem_str == 1) {
                                //это нам не надо
                            }
                            if ($count_elem_str == 2) {
                                $name = pq($cell)->text();
                                $link_url = pq($cell)->find('a')->eq(0)->attr('href');

                                $arrayTorrentsNEXT[$count_elem]['name'] = $name;
                                $arrayTorrentsNEXT[$count_elem]['download'] = '';
                                $arrayTorrentsNEXT[$count_elem]['magnet'] = '';
                                $arrayTorrentsNEXT[$count_elem]['url'] = 'https://kinozal.tv' . $link_url;

                                // Выводим содержимое ячейки
                                //echo pq($cell)->text() . "<br>";
                                //echo pq($cell)->html() . "<br>";

                                //echo '#1 (DOWNLOAD): ' . $link_download . "<br>";
                                //echo '#2 (MAGNET): ' . $link_magnet . "<br>";
                            }
                            if ($count_elem_str == 3) {
                                $count_comment = pq($cell)->text();
                                $arrayTorrentsNEXT[$count_elem]['count_comment'] = $count_comment;
                            }
                            if ($count_elem_str == 4) {
                                $size = pq($cell)->text();
                                if (strpos($size, 'ГБ') !== false) {
                                    $size = str_replace('ГБ', 'GB', $size);
                                }
                                if (strpos($size, 'МБ') !== false) {
                                    $size = str_replace('МБ', 'MB', $size);
                                }
                                $arrayTorrentsNEXT[$count_elem]['size'] = $size;
                            }
                            if ($count_elem_str == 5) {
                                $pirs_up = pq($cell)->text();
                            }
                            if ($count_elem_str == 6) {
                                $pirs_down = pq($cell)->text();

                                $arrayTorrentsNEXT[$count_elem]['distribute'] = $pirs_up;
                                $arrayTorrentsNEXT[$count_elem]['pumping'] = $pirs_down;

                                $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $pirs_up . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $pirs_down . '</span>';

                                $arrayTorrentsNEXT[$count_elem]['pirs'] = $pirs_html;
                            }

                            if ($count_elem_str == 7) {
                                $date_string = trim(pq($cell)->text());
                                $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                                $data_date = explode(" ", $date_string);

                                $new_date = $data_date[0] . '-' . $data_date[1] . '-' . $data_date[2];

                                /*
                                $date_timestamp = strtotime($new_date);
                                $date_formatted = date('d.m.Y', $date_timestamp);

                                $date = $date_formatted;*/

                                $date = date("d-m-Y", strtotime($new_date));
                                $arrayTorrentsNEXT[$count_elem]['date_publish'] = $date;
                            }
                            if ($count_elem_str == 8) {
                                //это нам не надо
                            }
                            $count_elem_str++;
                        }
                        $count_elem++;
                        //echo "<br>";
                    }
                    $count_rows++;

                }
                $arrayTorrents = array_merge($arrayTorrents, $arrayTorrentsNEXT);
            }
        }
        return $arrayTorrents;
    }

    public function ParseRUTOR($search_str)
    {

        //$search_str = 'Пацаны';
        // Адрес прокси-сервера
        $proxy = 'proxy.example.com:8080';
        // Создаем новый объект cURL
        $ch = curl_init();

        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
        );

        // Устанавливаем параметры запроса
        curl_setopt($ch, CURLOPT_URL, 'http://rutor.info/search/0/0/000/0/' . $search_str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Выполняем запрос и получаем ответ
        $response = curl_exec($ch);
        // Закрываем соединение cURL
        curl_close($ch);

        // Получаем HTML-код страницы результатов поиска
        $url = 'http://rutor.info/search/0/0/000/0/' . $search_str;
        //$html = file_get_contents($url);

        $html = $response;
        // Создаем объект phpQuery и загружаем HTML-код страницы результатов поиска
        $doc = phpQuery::newDocument($html);

        // Получаем список элементов с заголовками торрентов на странице
        //$results = $doc->find('.gai a');
        //$results = $doc->find('.gai');


        $arrayTorrents = array();
        $count_elem = 0;

        $rows = $doc->find('#index tr');
        $count_rows = 1;
        foreach ($rows as $row) {
            if ($count_rows != 1) {
                // Обходим ячейки строки
                $cells = pq($row)->find('td');

                $count_elem_str = 1;
                foreach ($cells as $cell) {
                    if (count($cells) == 5) {
                        //Date
                        if ($count_elem_str == 1) {
                            $date_string = trim(pq($cell)->text());

                            $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                            $data_date = explode(" ", $date_string);

                            if (strcasecmp($data_date[1], 'Янв'))
                                $month = '01';
                            if (strcasecmp($data_date[1], 'Фев'))
                                $month = '02';
                            if (strcasecmp($data_date[1], 'Мар'))
                                $month = '03';
                            if (strcasecmp($data_date[1], 'Апр'))
                                $month = '04';
                            if (strcasecmp($data_date[1], 'Май'))
                                $month = '05';
                            if (strcasecmp($data_date[1], 'Июн'))
                                $month = '06';
                            if (strcasecmp($data_date[1], 'Июл'))
                                $month = '07';
                            if (strcasecmp($data_date[1], 'Авг'))
                                $month = '08';
                            if (strcasecmp($data_date[1], 'Сен'))
                                $month = '09';
                            if (strcasecmp($data_date[1], 'Окт'))
                                $month = '10';
                            if (strcasecmp($data_date[1], 'Ноя'))
                                $month = '11';
                            if (strcasecmp($data_date[1], 'Дек'))
                                $month = '12';

                            $new_date = $data_date[0] . '-' . $month . '-' . $data_date[2];

                            $date_create = DateTime::createFromFormat('d-m-y', $new_date);
                            $date_formatted = $date_create->format('d-m-Y');

                            $date = $date_formatted;
                            $arrayTorrents[$count_elem]['date_publish'] = $date;
                        }
                        if ($count_elem_str == 2) {
                            $name = pq($cell)->text();
                            $link_download = pq($cell)->find('a')->eq(0)->attr('href');
                            $link_magnet = pq($cell)->find('a')->eq(1)->attr('href');
                            $link_url = pq($cell)->find('a')->eq(2)->attr('href');

                            $arrayTorrents[$count_elem]['name'] = $name;
                            $arrayTorrents[$count_elem]['download'] = $link_download;
                            $arrayTorrents[$count_elem]['magnet'] = $link_magnet;
                            $arrayTorrents[$count_elem]['url'] = 'http://rutor.info' . $link_url;
                            // Выводим содержимое ячейки
                            //echo pq($cell)->text() . "<br>";
                            //echo pq($cell)->html() . "<br>";

                            //echo '#1 (DOWNLOAD): ' . $link_download . "<br>";
                            //echo '#2 (MAGNET): ' . $link_magnet . "<br>";
                        }
                        if ($count_elem_str == 3) {
                            $count_comment = pq($cell)->text();
                            $arrayTorrents[$count_elem]['count_comment'] = $count_comment;
                        }
                        if ($count_elem_str == 4) {
                            $size = pq($cell)->text();
                            $arrayTorrents[$count_elem]['size'] = $size;
                        }
                        if ($count_elem_str == 5) {
                            $pirs = pq($cell)->html();

                            $text_pirs = trim(pq($pirs)->text());
                            $clear_pirs_tags = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $text_pirs);
                            $data_pirs = explode(" ", $clear_pirs_tags);

                            $arrayTorrents[$count_elem]['distribute'] = $data_pirs[1];
                            $arrayTorrents[$count_elem]['pumping'] = $data_pirs[5];

                            $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $data_pirs[1] . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $data_pirs[5] . '</span>';

                            $arrayTorrents[$count_elem]['pirs'] = $pirs_html;
                        }
                    }
                    if (count($cells) == 4) {
                        if ($count_elem_str == 1) {
                            $date_string = trim(pq($cell)->text());

                            $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                            $data_date = explode(" ", $date_string);

                            if (strcasecmp($data_date[1], 'Янв'))
                                $month = '01';
                            if (strcasecmp($data_date[1], 'Фев'))
                                $month = '02';
                            if (strcasecmp($data_date[1], 'Мар'))
                                $month = '03';
                            if (strcasecmp($data_date[1], 'Апр'))
                                $month = '04';
                            if (strcasecmp($data_date[1], 'Май'))
                                $month = '05';
                            if (strcasecmp($data_date[1], 'Июн'))
                                $month = '06';
                            if (strcasecmp($data_date[1], 'Июл'))
                                $month = '07';
                            if (strcasecmp($data_date[1], 'Авг'))
                                $month = '08';
                            if (strcasecmp($data_date[1], 'Сен'))
                                $month = '09';
                            if (strcasecmp($data_date[1], 'Окт'))
                                $month = '10';
                            if (strcasecmp($data_date[1], 'Ноя'))
                                $month = '11';
                            if (strcasecmp($data_date[1], 'Дек'))
                                $month = '12';

                            $new_date = $data_date[0] . '-' . $month . '-' . $data_date[2];

                            $date_create = DateTime::createFromFormat('d-m-y', $new_date);
                            $date_formatted = $date_create->format('d-m-Y');

                            $date = $date_formatted;
                            $arrayTorrents[$count_elem]['date_publish'] = $date;
                        }
                        if ($count_elem_str == 2) {
                            $name = pq($cell)->text();
                            $arrayTorrents[$count_elem]['name'] = $name;
                            $link_download = pq($cell)->find('a')->eq(0)->attr('href');
                            $link_magnet = pq($cell)->find('a')->eq(1)->attr('href');
                            $link_url = pq($cell)->find('a')->eq(2)->attr('href');

                            $arrayTorrents[$count_elem]['name'] = $name;
                            $arrayTorrents[$count_elem]['download'] = $link_download;
                            $arrayTorrents[$count_elem]['magnet'] = $link_magnet;
                            $arrayTorrents[$count_elem]['url'] = 'http://rutor.info' . $link_url;
                            //$link = pq($result)->find('a')->eq(1)->attr('href');
                            //echo $title . ': ' . $link;
                            $arrayTorrents[$count_elem]['count_comment'] = '0';
                        }
                        if ($count_elem_str == 3) {
                            $size = pq($cell)->text();
                            $arrayTorrents[$count_elem]['size'] = $size;
                        }
                        if ($count_elem_str == 4) {
                            $pirs = pq($cell)->html();

                            $text_pirs = trim(pq($pirs)->text());
                            $clear_pirs_tags = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $text_pirs);
                            $data_pirs = explode(" ", $clear_pirs_tags);
                            $arrayTorrents[$count_elem]['distribute'] = $data_pirs[1];
                            $arrayTorrents[$count_elem]['pumping'] = $data_pirs[5];

                            $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $data_pirs[1] . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $data_pirs[5] . '</span>';

                            $arrayTorrents[$count_elem]['pirs'] = $pirs_html;
                        }
                    }
                    $count_elem_str++;
                }
                $count_elem++;
                //echo "<br>";
            }
            $count_rows++;

        }
        //var_dump($arrayTorrents);


        // Получаем ссылку на следующую страницу результатов поиска, если она есть
        //$nextPageLink = $doc->find('.pager a:contains("След.")')->attr('href');
        //$nextPageLink = $doc->find('#index b a')->end()->attr('href');

        $pages = $doc->find('#index table + b a', 0);
        $count_page = count($pages);
        //$last_page_link = $doc->find('#index table + b a:last-of-type')->attr('href');
        if ($count_page > 0) {
            for ($i = 1; $i <= $count_page; $i++) {

                // Адрес прокси-сервера
                $proxy = 'proxy.example.com:8080';
                // Создаем новый объект cURL
                $ch = curl_init();

                $headers = array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
                );

                // Устанавливаем параметры запроса
                curl_setopt($ch, CURLOPT_URL, 'http://rutor.info/search/' . $i . '/0/000/0/' . $search_str);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($ch, CURLOPT_PROXY, $proxy);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Выполняем запрос и получаем ответ
                $response_next = curl_exec($ch);

                // Закрываем соединение cURL
                curl_close($ch);

                // Получаем HTML-код страницы результатов поиска
                $url = 'http://rutor.info/search/' . $i . '/0/000/0/' . $search_str;
                //$html = file_get_contents($url);

                $html = $response_next;

                // Создаем объект phpQuery и загружаем HTML-код страницы результатов поиска
                $doc = phpQuery::newDocument($html);

                // Получаем список элементов с заголовками торрентов на странице
                //$results = $doc->find('.gai a');
                //$results = $doc->find('.gai');


                $arrayTorrentsNEXT = array();
                $count_elem = 0;

                $rows = $doc->find('#index tr');
                $count_rows = 1;
                foreach ($rows as $row) {
                    if ($count_rows != 1) {
                        // Обходим ячейки строки
                        $cells = pq($row)->find('td');

                        $count_elem_str = 1;
                        foreach ($cells as $cell) {
                            if (count($cells) == 5) {
                                //Date
                                if ($count_elem_str == 1) {
                                    $date_string = trim(pq($cell)->text());

                                    $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                                    $data_date = explode(" ", $date_string);

                                    if (strcasecmp($data_date[1], 'Янв'))
                                        $month = '01';
                                    if (strcasecmp($data_date[1], 'Фев'))
                                        $month = '02';
                                    if (strcasecmp($data_date[1], 'Мар'))
                                        $month = '03';
                                    if (strcasecmp($data_date[1], 'Апр'))
                                        $month = '04';
                                    if (strcasecmp($data_date[1], 'Май'))
                                        $month = '05';
                                    if (strcasecmp($data_date[1], 'Июн'))
                                        $month = '06';
                                    if (strcasecmp($data_date[1], 'Июл'))
                                        $month = '07';
                                    if (strcasecmp($data_date[1], 'Авг'))
                                        $month = '08';
                                    if (strcasecmp($data_date[1], 'Сен'))
                                        $month = '09';
                                    if (strcasecmp($data_date[1], 'Окт'))
                                        $month = '10';
                                    if (strcasecmp($data_date[1], 'Ноя'))
                                        $month = '11';
                                    if (strcasecmp($data_date[1], 'Дек'))
                                        $month = '12';

                                    $new_date = $data_date[0] . '-' . $month . '-' . $data_date[2];

                                    $date_create = DateTime::createFromFormat('d-m-y', $new_date);
                                    $date_formatted = $date_create->format('d-m-Y');

                                    $date = $date_formatted;
                                    $arrayTorrentsNEXT[$count_elem]['date_publish'] = $date;
                                }
                                if ($count_elem_str == 2) {
                                    $name = pq($cell)->text();
                                    $link_download = pq($cell)->find('a')->eq(0)->attr('href');
                                    $link_magnet = pq($cell)->find('a')->eq(1)->attr('href');
                                    $link_url = pq($cell)->find('a')->eq(2)->attr('href');

                                    $arrayTorrentsNEXT[$count_elem]['name'] = $name;
                                    $arrayTorrentsNEXT[$count_elem]['download'] = $link_download;
                                    $arrayTorrentsNEXT[$count_elem]['magnet'] = $link_magnet;
                                    $arrayTorrentsNEXT[$count_elem]['url'] = 'http://rutor.info' . $link_url;
                                    // Выводим содержимое ячейки
                                    //echo pq($cell)->text() . "<br>";
                                    //echo pq($cell)->html() . "<br>";

                                    //echo '#1 (DOWNLOAD): ' . $link_download . "<br>";
                                    //echo '#2 (MAGNET): ' . $link_magnet . "<br>";
                                }
                                if ($count_elem_str == 3) {
                                    $count_comment = pq($cell)->text();
                                    $arrayTorrentsNEXT[$count_elem]['count_comment'] = $count_comment;
                                }
                                if ($count_elem_str == 4) {
                                    $size = pq($cell)->text();
                                    $arrayTorrentsNEXT[$count_elem]['size'] = $size;
                                }
                                if ($count_elem_str == 5) {
                                    $pirs = pq($cell)->html();

                                    $text_pirs = trim(pq($pirs)->text());
                                    $clear_pirs_tags = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $text_pirs);
                                    $data_pirs = explode(" ", $clear_pirs_tags);
                                    $arrayTorrentsNEXT[$count_elem]['distribute'] = $data_pirs[1];
                                    $arrayTorrentsNEXT[$count_elem]['pumping'] = $data_pirs[5];

                                    $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $data_pirs[1] . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $data_pirs[5] . '</span>';

                                    $arrayTorrentsNEXT[$count_elem]['pirs'] = $pirs_html;
                                }
                            }
                            if (count($cells) == 4) {
                                if ($count_elem_str == 1) {
                                    $date_string = trim(pq($cell)->text());

                                    $date_string = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $date_string);
                                    $data_date = explode(" ", $date_string);

                                    if (strcasecmp($data_date[1], 'Янв'))
                                        $month = '01';
                                    if (strcasecmp($data_date[1], 'Фев'))
                                        $month = '02';
                                    if (strcasecmp($data_date[1], 'Мар'))
                                        $month = '03';
                                    if (strcasecmp($data_date[1], 'Апр'))
                                        $month = '04';
                                    if (strcasecmp($data_date[1], 'Май'))
                                        $month = '05';
                                    if (strcasecmp($data_date[1], 'Июн'))
                                        $month = '06';
                                    if (strcasecmp($data_date[1], 'Июл'))
                                        $month = '07';
                                    if (strcasecmp($data_date[1], 'Авг'))
                                        $month = '08';
                                    if (strcasecmp($data_date[1], 'Сен'))
                                        $month = '09';
                                    if (strcasecmp($data_date[1], 'Окт'))
                                        $month = '10';
                                    if (strcasecmp($data_date[1], 'Ноя'))
                                        $month = '11';
                                    if (strcasecmp($data_date[1], 'Дек'))
                                        $month = '12';

                                    $new_date = $data_date[0] . '-' . $month . '-' . $data_date[2];

                                    $date_create = DateTime::createFromFormat('d-m-y', $new_date);
                                    $date_formatted = $date_create->format('d-m-Y');

                                    $date = $date_formatted;
                                    $arrayTorrentsNEXT[$count_elem]['date_publish'] = $date;
                                }
                                if ($count_elem_str == 2) {
                                    $name = pq($cell)->text();
                                    $arrayTorrentsNEXT[$count_elem]['name'] = $name;
                                    $link_download = pq($cell)->find('a')->eq(0)->attr('href');
                                    $link_magnet = pq($cell)->find('a')->eq(1)->attr('href');
                                    $link_url = pq($cell)->find('a')->eq(2)->attr('href');

                                    $arrayTorrentsNEXT[$count_elem]['name'] = $name;
                                    $arrayTorrentsNEXT[$count_elem]['download'] = $link_download;
                                    $arrayTorrentsNEXT[$count_elem]['magnet'] = $link_magnet;
                                    $arrayTorrentsNEXT[$count_elem]['url'] = 'http://rutor.info' . $link_url;
                                    //$link = pq($result)->find('a')->eq(1)->attr('href');
                                    //echo $title . ': ' . $link;
                                    $arrayTorrentsNEXT[$count_elem]['count_comment'] = '0';
                                }
                                if ($count_elem_str == 3) {
                                    $size = pq($cell)->text();
                                    $arrayTorrentsNEXT[$count_elem]['size'] = $size;
                                }
                                if ($count_elem_str == 4) {
                                    $pirs = pq($cell)->html();

                                    $text_pirs = trim(pq($pirs)->text());
                                    $clear_pirs_tags = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $text_pirs);
                                    $data_pirs = explode(" ", $clear_pirs_tags);
                                    $arrayTorrentsNEXT[$count_elem]['distribute'] = $data_pirs[1];
                                    $arrayTorrentsNEXT[$count_elem]['pumping'] = $data_pirs[5];

                                    $pirs_html = '<span class="green"><img src="/catalog/view/theme/default/image/arrowup.gif" title="Distribute" alt="Distribute"> ' . $data_pirs[1] . '</span> <img src="/catalog/view/theme/default/image/arrowdown.gif" title="Pumping" alt="Pumping"><span class="red"> ' . $data_pirs[5] . '</span>';

                                    $arrayTorrentsNEXT[$count_elem]['pirs'] = $pirs_html;
                                }
                            }
                            $count_elem_str++;
                        }
                        $count_elem++;
                        //echo "<br>";
                    }
                    $count_rows++;

                }
                $arrayTorrents = array_merge($arrayTorrents, $arrayTorrentsNEXT);
            }
        }

        //$doc = phpQuery::newDocumentHTML('<div>Hello, World!</div>');
        //echo $doc->find('div')->text(); // выведет "Hello, World!"
        //var_dump($arrayTorrents);
        return $arrayTorrents;
    }

}
