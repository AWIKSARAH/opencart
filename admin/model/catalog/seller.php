<?php
class ModelCatalogSeller extends Model
{
	public function addSeller($data)
	{
		$this->event->trigger('pre.admin.seller.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "seller SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int) $data['sort_order'] . "'");

		$seller_id = $this->db->getLastId();


		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int) $seller_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('seller');

		$this->event->trigger('post.admin.seller.add', $seller_id);

		return $seller_id;
	}
	public function editSeller($seller_id, $data)
	{
		$this->event->trigger('pre.admin.seller.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "seller SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int) $data['sort_order'] . "' WHERE seller_id = '" . (int) $seller_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int) $seller_id . "'");

		$this->cache->delete('seller');

		$this->event->trigger('post.admin.seller.edit');
	}



	public function deleteSeller($seller_id)
	{
		$this->event->trigger('pre.admin.seller.delete', $seller_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "seller WHERE seller_id = '" . (int) $seller_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int) $seller_id . "'");

		$this->cache->delete('seller');

		$this->event->trigger('post.admin.seller.delete', $seller_id);
	}

	public function getSeller($seller_id)
	{
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int) $seller_id . "') AS keyword FROM " . DB_PREFIX . "seller WHERE seller_id = '" . (int) $seller_id . "'");

		return $query->row;
	}

	public function getSellers($data = array())
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "seller";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getTotalSeller()
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "seller");

		return $query->row['total'];
	}
}