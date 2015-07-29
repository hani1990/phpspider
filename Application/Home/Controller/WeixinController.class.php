<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;


class WeixinController extends Controller {
	
	var $header = array( "User-Agent : Mozilla/5.0 (iPhone; CPU iPhone OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9B176 MicroMessenger/4.3.2" ,
			
						"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
						"Accept-Language: zh,zh-cn;q=0.8,en-us;q=0.5,en;q=0.3",
						"Cookie: Hm_lvt_aedd3972ca50f4fd67b4d7e025fa000c=1421985654,1422084096,1422084097,1422176563; bdshare_firstime=1421560690892; PHPSESSID=hu43skm3rnkof8qdvdngqmpnq7; Hm_lpvt_aedd3972ca50f4fd67b4d7e025fa000c=1422176873; sso_back_url=%7B%220%22%3A%22index%5C%2Fintern%22%2C%22id%22%3A8395%7D",
			);

	public function index(){

		$this->display();
	}

    public function run(){

    	$this->run_urls( 100 );

    }
/**
*  执行url 抓取数据 
* @param $count 读取的条数
*/

    public function run_urls( $count ){

    	$map['states'] = 0;
        $urls = M('urls')->where($map)->limit( $count )->select(); //设置 limit 避免太大
        if( $urls ){//存在没有抓取的urls

            foreach( $urls  as &$url ){
               //入库，并设置 urls表states 状态为1
               if ( !$html = url2html( $url['urlname'] ) ){ //获取 html错误

                    $map_id['id'] = $url['id'];
                    M('urls')->where($map_id)->setField('states',-1);   

               } else {

                   if ( $data = $this->temp_weixin($html , $url['urlname'] ) ){

                        if( M('blog')->add($data) ){

                            echo "ok";

                            $map_id['id'] = $url['id'];
                            M('urls')->where($map_id)->setField('states',1);   

                        } else {

                            echo 'error : '.$url['urlname'];
                            write_log('插入文章数据失败,'.$url['urlname']);
                        }
                 
                    } else { //入库出错 设置状态为2
                            $map_id['id'] = $url['id'];
                            M('urls')->where($map_id)->setField('states',2);  
                   }
               }


           }
        } else {
            echo "没有待抓取的urls";
            write_log('没有待抓取的urls');
        }


    }

//微信文章模板    

    public function temp_weixin( $html , $url ){

    	header( "Content-Type:text/html;charset:utf-8" );
    	import( Org.Net.simple_html_dom );

    	$data['title']     =  $html->find('title',0)->plaintext; //文章详情的标题
    	$content           =  $html->find( '#page-content' , 0 )->outertext; //文章文字内容
        $data['content']   =  $content." <a href='".$url."' target='_blank'> 原文链接 </a> ";
    	$date              =  $html->find( '#post-date' , 0 )->plaintext;  //文章更新时间
    	$data['date']      =  strtotime( $date );
    	//$data['post-user'] =  $html->find( '#post-user' , 0 )->plaintext;   //文章作者
        $data['excerpt']   =  "...";//msubstr( $data['content'] ,0 , 30);  //文章描述
        $data['sortid']    = 4;  //文章分类编号
        $data['author']    = 6;
        $data['views']      = rand( 13, 62);
     	$html->clear();
    	return $data;
    }

//抓取列表页的文章链接
    public function urls(){ 
    
        $url  = 'http://weixin.sogou.com/'; 
        header( "Content-Type:text/html;charset:utf-8" );

        import( Org.Net.simple_html_dom );
        $html = url2html( $url , $this->$header ); 

        $hrefs = $html->find( 'a' ); //获取所有的链接

        foreach ($hrefs as $url ) {

             $this->insert_urls( $url->href );
        }

        $html->clear();
    }

    public function insert_urls( $url ){



    		if( strpos( $url , 'http://mp.weixin.qq.com/' ) ){ //过滤只包含  http://mp.weixin.qq.com/  的链接

	    		//echo $h->href.'</br>';
	    		//入库操作
	    		$data['urlname'] = $url; //链接
	    		$data['ctime']   = time();
	    		$data['hash']	 = hash('sha224',$data['urlname']);  //hash url
	    		$map['hash']     = $data['hash'];
				if ( M('urls')->where($map)->getField('id') ){

					echo "重复";

				} else {
					//入库
					M('urls')->add($data);
					echo ' insert into database success ';
				}

    		} 

    	


    
    }

    public function get_weixin_article(){

        $url = I('url');

        $this->insert_urls( $url );
        $this->run_urls(1);

    }

}