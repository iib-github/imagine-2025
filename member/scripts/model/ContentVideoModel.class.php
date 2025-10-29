<?php
require_once dirname(__FILE__) . '/../BaseModel.class.php';

/**
 * コンテンツ動画管理クラス
 * コンテンツに紐づく動画の作成、取得、更新、削除機能を提供
 */
class ContentVideoModel extends BaseModel {

    public function __construct() {
        parent::set_table_name('content_video');
    }

    /**
     * コンテンツIDに紐づく動画一覧を取得
     *
     * @param int $content_id コンテンツID
     * @param array $order_data ソート条件（オプション）
     * @return array 動画一覧
     */
    public function getVideosByContentId($content_id, $order_data = null) {
        $where_data = array('content_id' => $content_id);
        
        // デフォルトは表示順序でソート
        if ($order_data === null) {
            $order_data = array('display_order' => BaseModel::ORDER_ASC);
        }
        
        return parent::select($where_data, $order_data);
    }

    /**
     * 動画IDで動画を取得
     *
     * @param int $video_id 動画ID
     * @return array|null 動画情報、存在しない場合はnull
     */
    public function getVideoById($video_id) {
        $videos = parent::select(array('video_id' => $video_id));
        return !empty($videos) ? $videos[0] : null;
    }

    /**
     * 動画を登録
     *
     * @param array $data 動画情報
     * @return bool 成功時true、失敗時false
     */
    public function registerVideo($data) {
        // 必須フィールドのチェック
        if (empty($data['content_id']) || empty($data['video_url']) || empty($data['video_title'])) {
            return false;
        }

        // display_orderが指定されていない場合は自動設定
        if (!isset($data['display_order'])) {
            $data['display_order'] = $this->getNextDisplayOrder($data['content_id']);
        }

        $result = parent::insert($data);
        if($result) {
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * 動画を更新
     *
     * @param array $data 更新データ
     * @param array $where_data 更新条件
     * @return bool 成功時true、失敗時false
     */
    public function updateVideo($data, $where_data) {
        return parent::update($data, $where_data);
    }

    /**
     * 動画を削除
     *
     * @param int $video_id 動画ID
     * @return bool 成功時true、失敗時false
     */
    public function deleteVideo($video_id) {
        return parent::delete(array('video_id' => $video_id));
    }

    /**
     * コンテンツに紐づく動画を全て削除
     *
     * @param int $content_id コンテンツID
     * @return bool 成功時true、失敗時false
     */
    public function deleteVideosByContentId($content_id) {
        return parent::delete(array('content_id' => $content_id));
    }

    /**
     * 次の表示順序を取得
     *
     * @param int $content_id コンテンツID
     * @return int 次の表示順序
     */
    public function getNextDisplayOrder($content_id) {
        $pdo = PdoInterface::getInstance();
        
        $sql = "SELECT MAX(display_order) as max_order FROM " . $this->table_name . " WHERE content_id = ?";
        $pdo->query($sql, array($content_id));
        
        $result = $pdo->fetch_assoc();
        return ($result['max_order'] !== null) ? $result['max_order'] + 1 : 1;
    }

    /**
     * 動画の表示順序を更新
     *
     * @param int $video_id 動画ID
     * @param int $new_order 新しい表示順序
     * @return bool 成功時true、失敗時false
     */
    public function updateDisplayOrder($video_id, $new_order) {
        return parent::update(
            array('display_order' => $new_order),
            array('video_id' => $video_id)
        );
    }

    /**
     * 動画の表示順序を一括更新
     *
     * @param array $video_orders 動画IDと表示順序の配列 [video_id => display_order]
     * @return bool 成功時true、失敗時false
     */
    public function updateDisplayOrders($video_orders) {
        $pdo = PdoInterface::getInstance();
        
        try {
            $pdo->beginTransaction();
            
            foreach ($video_orders as $video_id => $display_order) {
                $result = parent::update(
                    array('display_order' => $display_order),
                    array('video_id' => $video_id)
                );
                
                if (!$result) {
                    $pdo->rollback();
                    return false;
                }
            }
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollback();
            return false;
        }
    }

    /**
     * コンテンツの動画数を取得
     *
     * @param int $content_id コンテンツID
     * @return int 動画数
     */
    public function getVideoCountByContentId($content_id) {
        return parent::count(array('content_id' => $content_id));
    }

    /**
     * 動画の存在チェック
     *
     * @param int $video_id 動画ID
     * @return bool 存在する場合true
     */
    public function existsVideo($video_id) {
        return $this->getVideoById($video_id) !== null;
    }

    /**
     * コンテンツに動画が存在するかチェック
     *
     * @param int $content_id コンテンツID
     * @return bool 動画が存在する場合true
     */
    public function hasVideos($content_id) {
        return $this->getVideoCountByContentId($content_id) > 0;
    }

    /**
     * 動画一覧をコンテンツ情報と結合して取得
     *
     * @param int $content_id コンテンツID
     * @param array $order_data ソート条件（オプション）
     * @return array 動画一覧（コンテンツ情報含む）
     */
    public function getVideosWithContentInfo($content_id, $order_data = null) {
        $pdo = PdoInterface::getInstance();
        
        $sql = "SELECT 
                    cv.video_id,
                    cv.content_id,
                    cv.video_url,
                    cv.video_title,
                    cv.thumbnail_url,
                    cv.display_order,
                    cv.created_date,
                    cv.modified_date,
                    cm.content_title,
                    cm.content_text
                FROM " . $this->table_name . " cv
                LEFT JOIN content_master cm ON cv.content_id = cm.content_id
                WHERE cv.content_id = ?";
        
        // ソート条件の追加
        if ($order_data !== null) {
            $order_clause = '';
            foreach ($order_data as $field => $direction) {
                if ($direction === BaseModel::ORDER_ASC) {
                    $order_clause .= ", cv.{$field} ASC";
                } elseif ($direction === BaseModel::ORDER_DESC) {
                    $order_clause .= ", cv.{$field} DESC";
                }
            }
            if ($order_clause) {
                $sql .= " ORDER BY " . trim($order_clause, ",");
            }
        } else {
            $sql .= " ORDER BY cv.display_order ASC";
        }
        
        $pdo->query($sql, array($content_id));
        
        $result = array();
        while($rs = $pdo->fetch_assoc()) {
            $result[] = $rs;
        }
        return $result;
    }
}
