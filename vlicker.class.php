<?php
/**
 * VIANCH FLICKER Class
 *
 * @author Victor Chavarro {@link http://www.vianch.com Victor Chavarro (victor@vianch.com)}
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//@see 

/**
 * URL DE LAS CARGAS QUE SE HAN REALIZADO EN FLICKR, RETORNA UN JSON CON LA INFORMACIÓN 
 * (Se debe concatenar el id del usuario de flick, para obtener la id se puede entrar en la siguiente url: http://idgettr.com/)
 */ 
define("FLICKR_UPLOADS_URL","http://api.flickr.com/services/feeds/photos_public.gne?lang=%lang%&format=json&id=%flickr_id%"); 

class vlicker{

	/*$flick_id es el identificador del usuario en flickr*/
	private $flickr_id;

	/*Cantidad de imagenes a mostrar en la impresión*/
	private $count;

	/*Idiona en que será entregado el JSON*/
	private $lang;

	/*Tamaño de la imagen como será entregada*/
	private $image_size;

	/*URL que será consultada para obtener el JSON*/
	private $flickr_url;

	/**
	 * Función constructora de la clase, se inicializan las variables
	 *
	 * $flickr_id: Identificador del usuario en flickr, por defecto es null
	 *
	 * $count: cantidad de imagenes a imprimir, por defecto son 4
	 *
	 * $lang: idioma en que es retornado el JSON, si no se define el idioma por defecto es Ingles
	 * IDIOMAS SOPORTADOS:
	 *	de-de	    Alemán
	 *  en-us	    inglés
	 *	es-us	    Español
	 *	fr-fr	    Francés
	 *  it-it	    Italiano
	 *	ko-kr	    Coreano
	 *	pt-br	    Portugués (Brasileño)
	 *  zh-hk       Chino tradicional (Hong Kong) 
	 *
	 * $image_size es el tamago de la imagen en que sera entregado.
	 * TAMAÑOS DE LAS IMAGENES:
	 *	s	cuadrado pequeño 75x75
	 *	q	large square 150x150
	 *	t	imagen en miniatura, 100 en el lado más largo
	 *	m	pequeño, 240 en el lado más largo
	 *	n	small, 320 on longest side
	 *	z	mediano 640, 640 en el lado más largo
	 *	c	medium 800, 800 on longest side†
	 *	b	grande, 1024 en el lado más largo
	 *
	 * @param $flickr_id string
	 * @param $count int
	 * @param $lang string
	 * @param $image_size string
	 *
	 * @return bool
	 */
	public function __construct( $flickr_id = null, $count = 4,  $image_size = 's', $lang = "en-us" ){
		
		if( $flickr_id != null ){
		
			/*SE INICIALIZAN LAS VARIABLES DE LA CLASE*/
			$this->flickr_id = $flickr_id;
			$this->count = $count; // cantidad de fotos a mostrar
			$this->lang = $lang;
			$this->image_size = $image_size;


			/*SE INICIALIZA LA URL*/
			$reserved_words = array( '%flickr_id%','%lang%' ); //palabras que seran cambiadas en la URL de consulta, en este caso en FLICKR_UPLOADS_URL
			$url_values = array("$flickr_id","$lang"); //valores que seran colocados en la URL de consulta en este caso FLICKR_UPLOADS_URL
			$this->flickr_url = str_replace($reserved_words, $url_values, FLICKR_UPLOADS_URL);
			
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Realiza la conexión con flickr a traves de una URL, retorna el JSON con la información
	 * o falso si no se puede realizar la consulta
     * @return mixed
	 */ 

	private function flickr_connect(){
	
		$flickr_connect= curl_init(); //inicializacion CURL
		$timeout = 7; //sesión abierta por una hora

		/*PARAMETROS CURL*/
		curl_setopt($flickr_connect,CURLOPT_URL, $this->flickr_url); //Dirección URL a capturar
		curl_setopt($flickr_connect,CURLOPT_RETURNTRANSFER,1); //para devolver el resultado de la transferencia como string
		curl_setopt($flickr_connect,CURLOPT_CONNECTTIMEOUT,$timeout); //Número de segundos a esperar cuando se está intentado conectar.
		
		$flickr_data = curl_exec($flickr_connect); //se conecta a la url
		
		curl_close($flickr_connect); //cierra la conexión
		
		//si no se logra obtener datos retorna falso, de lo contrario retorne la información	
		if( ( !$flickr_data ) || ( $flickr_data === FALSE) ){
			return false;
		}
		else{
			return $flickr_data;
		}
	}

	/**
	 * Genera la lista de items de flickr dependiendo de la cantidad que indique en los parametros
	 * esta lista ya esta formateada en HTML y es retornada en un string para posteriormente ser 
	 * impresa en pantalla por otra función.
	 *
	 * @see vlicker_printer()
	 * @return string
	 */
	private function generate_image_list(){
		
		$list = '<ul>'; //lista de tweets ya formateados en html, $list es el string que retorna la función
		
		$flickr_info = $this->flickr_connect(); //obtiene la información de la consulta por URL
		$flickr_info = str_replace("jsonFlickrFeed(", "", $flickr_info); // quita la primera parte del json
		$flickr_info = substr($flickr_info, 0, -1); // quita el paréntesis final
		$flickr_info = json_decode($flickr_info); //decodifica el json lo pasa a un OArray
	
		
		
			$contador = 1;
			foreach($flickr_info->items AS $photo){

				$new_size = $this->image_size; //tamaño que se definio para ser entregada la imagen
				$image_title = $photo->title;
				$image_link = $photo->link;
				$image_url = str_replace("m.jpg", "$new_size".".jpg", $photo->media->m);

				$list .= "<li><a title='$image_title' href='$image_link' target='_blank'>";
				$list .= "<img width='44' height='44' alt='$image_title' src='$image_url' />";
				$list .= "</a></li>";
				++$contador;	
				/*retornar hasta la cantidad que se definieron en los parámetros*/
				if($contador > $this->count){
					break;
				}
			}
	
		$list .= "</ul>";

		return $list;
	}

	/**
	 * Imprime en pantalla la lista de imagenes de flicker
	 * @see generate_image_list()
	 */
	public function vlicker_printer(){
		echo $this->generate_image_list();
	}


	/**
	 * resetea el array de propiedades
	 */
	public function __destruct(){
		$this->vt_properties = array();
	}
}


$vlicker_info = new vlicker('77859701@N03',5,'s');
$vlicker_info->vlicker_printer();


?>

