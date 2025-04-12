<?php
declare(strict_types=1);

if(class_exists('T2G_Plugin')){
	return;
}
class T2G_Plugin
{
	private static function user_Ip():?string
	{
		if(wp_get_environment_type() === 'local'){
			return '41.155.60.149';
		}
		foreach ([
			         'HTTP_CLIENT_IP',
			         'HTTP_X_FORWARDED_FOR',
			         'HTTP_X_FORWARDED',
			         'HTTP_X_CLUSTER_CLIENT_IP',
			         'HTTP_FORWARDED_FOR',
			         'HTTP_FORWARDED',
			         'REMOTE_ADDR'
		         ] as $key) {
			if (!empty($_SERVER[$key])) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					$ip = trim($ip);
					if (filter_var($ip, FILTER_VALIDATE_IP)) {
						return $ip;
					}
				}
			}
		}
		return null;
	}

	private static function user_languages(string $ip_address): array
	{
//		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
//			return explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
//		}

		$cache = get_transient($ip_address);

		if($cache){
			return $cache;
		}

		$url = sprintf(GEO_API_URL, $ip_address);

		$response = file_get_contents($url);

		if(!$response){

			return[];
		}

		$data = json_decode($response, true);

		if ($data && isset($data['languages'])) {
			$languages = explode(',', $data['languages']);
			set_transient($ip_address, $languages, 60 * 60);
			return $languages;
		}
		return [];
	}

	private static function current_page_translations(): array
	{
		switch (true) {
			case is_singular(['post', 'page']):
				return pll_get_post_translations(get_the_ID());

			case is_archive():
				return pll_get_term_translations(get_queried_object_id());

			default:
				return [ 'en' => get_queried_object_id() ];
		}
	}

	private static function current_page_locale(): string
	{
		switch (true) {
			case is_singular(['post', 'page']):
				return pll_get_post_language( get_the_ID() );

			case is_archive():
				return pll_get_term_language(get_queried_object_id());

			default:
				return 'en';
		}
	}
	private static function link(string $locale){


		switch (true) {
			case is_singular(['post', 'page']):
				return get_permalink(pll_get_post( get_the_ID(), $locale ));

			case is_archive():
				return get_term_link(pll_get_term(get_queried_object_id(), $locale));

			default:
				return '';
		}
	}

	private static function redirect(string $url): void
	{
		wp_safe_redirect($url);
		exit;
	}

	public static function filter_switch_url(string $url)
	{
		return add_query_arg(['from_switcher' => 1], $url);
	}
	private static function is_query_var(string $key){
		return  isset($_GET[$key]) || get_query_var($key);
	}
	private static function remove_query_var(string $key){
		unset($_GET[$key]);
	}
	public static function init()
	{
		if(is_user_logged_in() && current_user_can('edit_posts')){
			return;
		}

		if(is_front_page() && is_home()){
			return;
		}

		$from_switcher = self::is_query_var('from_switcher'); // phpcs:ignore;

		if($from_switcher){
			self::remove_query_var("from_switcher");
			return;
		}

		$ip = self::user_Ip();

		$user_languages = self::user_languages($ip);

		$page_translations = self::current_page_translations();
		$current_page_locale = self::current_page_locale();

		$first_language = $user_languages[0];

		$user_locale = explode('-', $first_language)[0];

		if($user_locale === $current_page_locale){
			return;
		}

		// If the user locale doesn't have a page version, redirect to the English version
		if(!isset($page_translations[$user_locale])){

			if(!in_array('en', array_keys($page_translations))){
				return;
			}

			self::redirect(self::link( 'en'));
		}
		self::redirect(self::link( $user_locale));
	}
}