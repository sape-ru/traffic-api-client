<?php

namespace SapeRt\Api\Client;

use SapeRt\Api\Config;
use SapeRt\Api\Exception\Exception;
use SapeRt\Api\Param\Page;

/**
 * Class User
 * @package SapeRt\Api\Client
 */
class User extends Base
{
    /**
     * @param Config $config
     */
    public function __construct($config = null)
    {
        if (!$config) {
            $config = new Config('/api/user');
        }

        parent::__construct($config);
    }

    /* Поддержка */

    /**
     * Добавить тикет в техподдержку
     *
     * @param string $type      Тип тикета
     * @param string $title     Заголовок
     * @param string $body      Текст
     * @param array  $filenames Загружаемые файлы
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-support.addfeedback
     */
    public function support_addfeedback($type, $title, $body,
                                        array $filenames = array())
    {
        $files = [];
        foreach ($filenames as $filename) {
            $files['file[]'][] = $filename;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['type' => $type],
            ['title' => $title, 'body' => $body], $files, true);
    }

    /* Разделы */

    /**
     * @param Page $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-sections.list
     */
    public function sections_list($page = null): array
    {
        $params = [];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-sections.add
     */
    public function sections_add($data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-sections.get
     */
    public function sections_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-sections.edit
     */
    public function sections_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-sections.delete
     */
    public function sections_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Объявления */

    /**
     * @param $campaign_id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.addbunch
     */
    public function ads_addbunch($campaign_id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['campaign_id' => $campaign_id], $data);
    }

    /**
     * @param $campaign_id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.add
     */
    public function ads_add($campaign_id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['campaign_id' => $campaign_id], $data);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.edit
     */
    public function ads_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.get
     */
    public function ads_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.erase
     */
    public function ads_erase($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param int|string $id_or_url
     * @param bool|null  $check_final_url
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.checkUrlAvailability
     */
    public function ads_checkUrlAvailability($id_or_url,
                                             $check_final_url = null): array
    {
        $params = [];

        if (isset($check_final_url)) {
            $params['check_final_url'] = $check_final_url;
        }

        if (is_numeric($id_or_url)) {
            $params['id'] = $id_or_url;
        } else {
            $params['url'] = $id_or_url;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param int        $campaign_id
     * @param array|null $filter
     * @param Page|null  $page
     * @param array      $params
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.list
     */
    public function ads_list($campaign_id = null, $filter = array(),
                             $page = null, array $params = array()): array
    {
        if ($campaign_id) {
            $params['campaign_id'] = $campaign_id;
        }

        if ($filter) {
            $params['filter'] = $filter;
        }

        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.start
     */
    public function ads_start($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-ads.stop
     */
    public function ads_stop($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Правила показа объявлений */

    /**
     * @param $campaign_id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.addbunch
     */
    public function adrules_addbunch($campaign_id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['campaign_id' => $campaign_id], $data);
    }

    /**
     * @param $campaign_id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.add
     */
    public function adrules_add($campaign_id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['campaign_id' => $campaign_id], $data);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.edit
     */
    public function adrules_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.get
     */
    public function adrules_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $campaign_id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.linkads
     */
    public function adrules_linkads($campaign_id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['campaign_id' => $campaign_id], $data);
    }

    /**
     * @param int  $campaign_id
     * @param int  $type
     * @param Page $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.list
     */
    public function adrules_list($campaign_id, $type = null,
                                 $page = null): array
    {
        $params = ['campaign_id' => $campaign_id];
        if ($type) {
            $params['type'] = $type;
        }
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adrules.delete
     */
    public function adrules_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Кампании */

    /**
     * @param      $data
     * @param null $preset_id
     * @param bool $is_draft
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.add
     */
    public function campaigns_add($data, $preset_id = null,
                                  $is_draft = false): array
    {
        $params = [];
        if ($preset_id) {
            $params['preset_id'] = $preset_id;
        }

        if ($is_draft) {
            $params['is_draft'] = $is_draft;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), $params, $data);
    }

    /**
     * @param      $id
     * @param      $data
     * @param bool $is_draft
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.edit
     */
    public function campaigns_edit($id, $data, $is_draft = false): array
    {
        $params = ['id' => $id];

        if ($is_draft) {
            $params['is_draft'] = $is_draft;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), $params, $data);
    }

    /**
     * @param      $id
     * @param null $json_type
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.get
     */
    public function campaigns_get($id, $json_type = null): array
    {
        $params['id'] = $id;
        if ($json_type) {
            $params['json_type'] = $json_type;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param array $filter
     * @param Page  $page
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.list
     */
    public function campaigns_list(array $filter = array(), $page = null)
    {
        $params = [];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        if ($filter) {
            $params['filter'] = $filter;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param      $id
     * @param bool $no_ads
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.start
     */
    public function campaigns_start($id, $no_ads = false): array
    {
        $params = ['id' => $id];

        if ($no_ads) {
            $params['no_ads'] = 1;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.stop
     */
    public function campaigns_stop($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.archive
     */
    public function campaigns_archive($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.erase
     */
    public function campaigns_erase($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param      $id
     * @param bool $with_ads
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-campaigns.clone
     */
    public function campaigns_clone($id, $with_ads = false): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['id' => $id, 'ads' => (int)$with_ads]);
    }

    /* Белые списки */

    /**
     * @param Page $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-listdomains.list
     */
    public function listdomains_list($page = null): array
    {
        $params = [];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-listdomains.add
     */
    public function listdomains_add($data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-listdomains.edit
     */
    public function listdomains_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-listdomains.get
     */
    public function listdomains_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-listdomains.delete
     */
    public function listdomains_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Креативы */

    /**
     * @param      $filename
     * @param null $type
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.add
     */
    public function creatives_add($filename, $type = null): array
    {
        if (!file_exists($filename)) {
            throw new Exception("File not exists: $filename");
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['type' => $type], [], ['file[]' => $filename]);
    }

    /**
     * @param $ids
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.delete
     */
    public function creatives_delete($ids): array
    {
        $data = ['creative_ids' => (array) $ids];

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $ids
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.erase
     */
    public function creatives_erase($ids): array
    {
        $data = ['creative_ids' => (array) $ids];

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param       $id
     * @param array $crops
     *      Массив с элементами в виде структуры:
     *      frame_id: 11 // ID Фрейма
     *      top: 20      // Отступ сверху
     *      left: 20     // Отступ слева
     *      width: 220   // Ширина
     *      height: 159  // Высота
     *
     *   Или массив с элементами в виде структуры:
     *      rect: { width: 180, height: 130 } // Целевая рамка
     *      top: 20      // Отступ сверху
     *      left: 20     // Отступ слева
     *      width: 220   // Ширина
     *      height: 159  // Высота
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.crop
     */
    public function creatives_crop($id, $crops)
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $crops);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.get
     */
    public function creatives_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * Получить шаблон по ID
     *
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adtemplates.get
     */
    public function adtemplates_get($id)
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * Получить список шаблонов подходящих для данного креатива вместе
     * с существующими кропами
     *
     * @param int $creative_id ID креатива
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-adtemplates.listbycreative
     */
    public function adtemplates_listbycreative($creative_id)
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__),
            ['creative_id' => $creative_id]);
    }

    /**
     * @param                        $filter
     * * is_parent
     * * frame_id
     * * date_created_from
     * * date_created_to
     * @param Page                   $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-creatives.list
     */
    public function creatives_list($filter, $page = null): array
    {
        $params = ['filter' => $filter];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /* Теги креативов */

    /**
     * @param $name
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-tags.add
     */
    public function tags_add($name): array
    {
        $data['name'] = $name;

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param      $id
     * @param      $name
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-tags.edit
     */
    public function tags_edit($id, $name): array
    {
        $params       = ['id' => $id];
        $data['name'] = $name;

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), $params, $data);
    }

    /**
     * @param array $filter
     * @param Page  $page
     *
     * @return mixed
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-tags.list
     */
    public function tags_list(array $filter = array(), $page = null)
    {
        $params = [];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        if ($filter) {
            $params['filter'] = $filter;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-tags.delete
     */
    public function tags_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Статистика */

    /**
     * @param $data_view_from
     * @param $data_view_to
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-statistics.total
     */
    public function statistics_total($data_view_from,
                                     $data_view_to): array
    {
        $filter = [
            'data_view_from' => $data_view_from,
            'data_view_to'   => $data_view_to,
        ];

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $filter);
    }

    /**
     * @param       $data_view_from
     * @param       $data_view_to
     * @param array $ids
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-statistics.ads
     */
    public function statistics_ads($data_view_from, $data_view_to,
                                   array $ids = array()): array
    {
        $filter = [
            'data_view_from' => $data_view_from,
            'data_view_to'   => $data_view_to,
        ];

        if ($ids) {
            $filter['ids'] = implode(',', $ids);
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $filter);
    }

    /**
     * @param       $data_view_from
     * @param       $data_view_to
     * @param array $ids
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-statistics.campaigns
     */
    public function statistics_campaigns($data_view_from, $data_view_to,
                                         array $ids = array()): array
    {
        $filter = [
            'data_view_from' => $data_view_from,
            'data_view_to'   => $data_view_to,
        ];

        if ($ids) {
            $filter['ids'] = implode(',', $ids);
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $filter);
    }

    /**
     * @param $campaign_id
     * @param $date_view_from
     * @param $date_view_to
     * @param $max_data_points
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-statistics.bidding
     */
    public function statistics_bidding($campaign_id, $date_view_from,
                                       $date_view_to, $max_data_points): array
    {
        $filter = [
            'campaign_id'     => $campaign_id,
            'date_view_from'  => $date_view_from,
            'date_view_to'    => $date_view_to,
            'max_data_points' => $max_data_points,
        ];

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $filter);
    }


    /* Сегменты */

    /**
     * @param array $filter
     * @param Page  $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-segments.list
     */
    public function segments_list($filter = [], $page = null): array
    {
        $params = [];

        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        $params['filter'] = $filter;

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param      $data
     * @param null $filename
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-segments.add
     */
    public function segments_add($data, $filename = null): array
    {
        $files = [];
        if ($filename) {
            $files['file'] = $filename;
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [],
            $data, $files, true);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-segments.get
     */
    public function segments_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param      $id
     * @param      $data
     * @param null $filename
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-segments.edit
     */
    public function segments_edit($id, $data, $filename = null): array
    {
        $files = [];
        if ($filename) {
            $files['file'] = $filename;
        }

        $data['online'] = true;

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__),
            ['id' => $id], $data, $files, true);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-segments.delete
     */
    public function segments_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Пиксели */

    /**
     * @param Page $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-pixels.list
     */
    public function pixels_list($page = null): array
    {
        $params = [];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-pixels.add
     */
    public function pixels_add($data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-pixels.get
     */
    public function pixels_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-pixels.edit
     */
    public function pixels_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-pixels.delete
     */
    public function pixels_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /* Маркетплейс */

    /**
     * @param array $filter
     * @param Page  $page
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.list
     */
    public function market_list(array $filter = [], $page = null): array
    {
        $params = ['filter' => $filter];
        if ($page) {
            $params = array_merge($params, $page->toArray());
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.cancel
     */
    public function market_cancel($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.approve
     */
    public function market_approve($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.add
     */
    public function market_add($data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.edit
     */
    public function market_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.get
     */
    public function market_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.providers
     */
    public function market_getMarketProviders(): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__));
    }

    /**
     * @param bool $names_only
     * @param null $provider_id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.groups
     */
    public function market_getMarketGroups($names_only = false,
                                           $provider_id = null): array
    {
        $params = [
            'names-only' => $names_only
        ];

        if ($provider_id !== null) {
            $params['provider_id'] = $provider_id;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param bool $names_only
     * @param null $group_id
     * @param null $type_name
     * @param null $provider_id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.attrs
     */
    public function market_getMarketAttrs(
        $names_only = false, $group_id = null,
        $type_name = null, $provider_id = null): array
    {
        $params = [
            'names-only' => $names_only
        ];

        if ($provider_id !== null) {
            $params['provider_id'] = $provider_id;
        }

        if ($group_id !== null) {
            $params['group_id'] = $group_id;
        }

        if ($type_name !== null) {
            $params['type_name'] = $type_name;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /**
     * @param bool $names_only
     * @param null $is_enum
     * @param null $provider_id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-market.types
     */
    public function market_getMarketTypes(
        $names_only = false, $is_enum = null,
        $provider_id = null): array
    {
        $params = [
            'names-only' => $names_only,
        ];

        if ($provider_id !== null) {
            $params['provider_id'] = $provider_id;
        }

        if ($is_enum !== null) {
            $params['is_enum'] = $is_enum;
        }

        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $params);
    }

    /* Сертификаты */

    /**
     * @param int   $id
     * @param array $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.bind
     */
    public function certificates_bind($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.list
     */
    public function certificates_list($data): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.get
     */
    public function certificates_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.relations
     */
    public function certificates_relations(): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__));
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.unbind
     */
    public function certificates_unbind($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.delete
     */
    public function certificates_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $data
     * @param $filename
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.add
     */
    public function certificates_add($data, $filename): array
    {
        if (!file_exists($filename)) {
            throw new Exception("File not exists: $filename");
        }

        $files = ['certificate' => $filename];

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [],
            $data, $files, true);
    }

    /**
     * @param $id
     * @param $data
     * @param $filename
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-certificates.edit
     */
    public function certificates_edit($id, $data, $filename): array
    {
        $files = [];
        if (file_exists($filename)) {
            $files = ['certificate' => $filename];
        }

        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id],
            $data, $files, false);
    }

    /* Клиенты */

    /**
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.add
     */
    public function clients_add($data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), [], $data);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.edit
     */
    public function clients_edit($id, $data): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id], $data);
    }

    /**
     * @param array $filter
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.list
     */
    public function clients_list($filter = []): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), $filter);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.get
     */
    public function clients_get($id): array
    {
        return $this->request(self::GET,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.delete
     */
    public function clients_delete($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws Exception
     * @link https://traffic.sape.ru/doc/api#action-clients.generatetoken
     */
    public function clients_generatetoken($id): array
    {
        return $this->request(self::POST,
            self::toEndPoint(__FUNCTION__), ['id' => $id]);
    }
}
