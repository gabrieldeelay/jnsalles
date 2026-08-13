<?php


class GenerateNumbers
{
	protected static $cotas = [];

	public static function generate_numbers($qty_numbers, $total_numbers_generated, $pid, $code)
	{
		global $_settings;
		$db = new DBConnection();
		$conn = $db->conn;
		$orders = $_settings->conn->query('SELECT order_numbers FROM order_list WHERE product_id = \'' . $pid . '\' AND status <> 3');
		$cotas_vendidas = [];

		while ($row = $orders->fetch_assoc()) {
			$cotas_vendidas[] = $row['order_numbers'];
		}

		$all_lucky_numbers = implode(',', $cotas_vendidas);
		$all_lucky_numbers = explode(',', $all_lucky_numbers);
		$numeros_ja_vendidos = array_filter($all_lucky_numbers);

		if ($qty_numbers < ($total_numbers_generated + count($numeros_ja_vendidos))) {
			$resp['status'] = 'failed';
			$conn->query('DELETE FROM `order_list` where code = \'' . $code . '\'');
			$conn->query('UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - \'' . $total_numbers_generated . '\' WHERE `id` = \'' . $pid . '\'');
			return json_encode($resp);
		}
		$cotas_vendidas = array_filter($cotas_vendidas);
		$qty_numbers = $qty_numbers - 1;
		self::set_quotes($cotas_vendidas);
		$globos = strlen($qty_numbers);
		$list_numbers = range(1, $qty_numbers);
		shuffle($list_numbers);
		$free_numbers = array_slice(array_diff($list_numbers, self::$cotas), 0, $total_numbers_generated);
		$order_numbers = array_map(function($item) use($qty_numbers, $globos) {
			return str_pad($item, max((int) $globos, strlen($qty_numbers)), '0', STR_PAD_LEFT);
		}, $free_numbers);
		return $order_numbers;
	}

	protected static function set_quotes($items = [])
	{
		if ($items) {
			$cota_formatada = array_map(function($item) {
				return array_map(function($cota) {
					return ltrim($cota, '0');
				}, explode(',', preg_replace('/\\s+/', '', $item)));
			}, $items);
			self::$cotas = array_merge(self::$cotas, ...$cota_formatada);
			self::$cotas = array_filter(self::$cotas);
		}
	}
}

?>