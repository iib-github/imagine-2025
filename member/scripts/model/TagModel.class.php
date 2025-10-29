<?php
require_once dirname(__FILE__) . '/../BaseModel.class.php';

/**
 * タグマスター管理クラス
 * タグの作成、取得、更新、削除機能を提供
 */
class TagModel extends BaseModel {

    public function __construct() {
        parent::set_table_name('tag_master');
    }

    /**
     * 全タグ一覧を取得
     *
     * @param array $where_data 検索条件（オプション）
     * @param array $order_data ソート条件（オプション）
     * @param array $limit_data リミット条件（オプション）
     * @return array タグ一覧
     */
    public function getTagList($where_data = null, $order_data = null, $limit_data = null) {
        return parent::select($where_data, $order_data, $limit_data);
    }

    /**
     * タグIDでタグを取得
     *
     * @param int $tag_id タグID
     * @return array|null タグ情報、存在しない場合はnull
     */
    public function getTagById($tag_id) {
        $tags = parent::select(array('tag_id' => $tag_id));
        return !empty($tags) ? $tags[0] : null;
    }

    /**
     * タグ名でタグを取得
     *
     * @param string $tag_name タグ名
     * @return array|null タグ情報、存在しない場合はnull
     */
    public function getTagByName($tag_name) {
        $tags = parent::select(array('tag_name' => $tag_name));
        return !empty($tags) ? $tags[0] : null;
    }

    /**
     * タグを登録
     *
     * @param array $data タグ情報
     * @return bool 成功時true、失敗時false
     */
    public function registerTag($data) {
        // 必須フィールドのチェック
        if (empty($data['tag_name'])) {
            return false;
        }

        // タグ名の重複チェック
        if ($this->getTagByName($data['tag_name']) !== null) {
            return false;
        }

        $result = parent::insert($data);
        if($result) {
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * タグを更新
     *
     * @param array $data 更新データ
     * @param array $where_data 更新条件
     * @return bool 成功時true、失敗時false
     */
    public function updateTag($data, $where_data) {
        // タグ名が変更される場合の重複チェック
        if (isset($data['tag_name'])) {
            $existing_tag = $this->getTagByName($data['tag_name']);
            if ($existing_tag !== null && $existing_tag['tag_id'] != $where_data['tag_id']) {
                return false;
            }
        }

        return parent::update($data, $where_data);
    }

    /**
     * タグを削除
     *
     * @param int $tag_id タグID
     * @return bool 成功時true、失敗時false
     */
    public function deleteTag($tag_id) {
        return parent::delete(array('tag_id' => $tag_id));
    }

    /**
     * タグ名で検索（部分一致）
     *
     * @param string $keyword 検索キーワード
     * @return array 検索結果
     */
    public function searchTags($keyword) {
        $pdo = PdoInterface::getInstance();
        
        $sql = "SELECT * FROM " . $this->table_name . " WHERE tag_name LIKE ? ORDER BY tag_name ASC";
        $pdo->query($sql, array('%' . $keyword . '%'));
        
        $result = array();
        while($rs = $pdo->fetch_assoc()) {
            $result[] = $rs;
        }
        return $result;
    }

    /**
     * 人気タグを取得（使用回数順）
     *
     * @param int $limit 取得件数
     * @return array 人気タグ一覧
     */
    public function getPopularTags($limit = 10) {
        $pdo = PdoInterface::getInstance();
        
        $sql = "SELECT 
                    t.tag_id,
                    t.tag_name,
                    t.tag_description,
                    COUNT(ctr.relation_id) as use_count
                FROM " . $this->table_name . " t
                LEFT JOIN content_tag_relation ctr ON t.tag_id = ctr.tag_id
                GROUP BY t.tag_id, t.tag_name, t.tag_description
                ORDER BY use_count DESC, t.tag_name ASC
                LIMIT ?";
        
        $pdo->query($sql, array($limit));
        
        $result = array();
        while($rs = $pdo->fetch_assoc()) {
            $result[] = $rs;
        }
        return $result;
    }

    /**
     * タグの使用回数を取得
     *
     * @param int $tag_id タグID
     * @return int 使用回数
     */
    public function getTagUseCount($tag_id) {
        $pdo = PdoInterface::getInstance();
        
        $sql = "SELECT COUNT(*) as use_count FROM content_tag_relation WHERE tag_id = ?";
        $pdo->query($sql, array($tag_id));
        
        $result = $pdo->fetch_assoc();
        return (int)$result['use_count'];
    }

    /**
     * タグの存在チェック
     *
     * @param int $tag_id タグID
     * @return bool 存在する場合true
     */
    public function existsTag($tag_id) {
        return $this->getTagById($tag_id) !== null;
    }

    /**
     * タグ名の存在チェック
     *
     * @param string $tag_name タグ名
     * @return bool 存在する場合true
     */
    public function existsTagName($tag_name) {
        return $this->getTagByName($tag_name) !== null;
    }
}
