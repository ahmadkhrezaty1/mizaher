<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('mec_display_price'))
{
  function mec_display_price($original_price=0, $sell_price=0, $currency_icon = '$',$retun_type='1') //$retun_type=1 means price overthrough, $retun_type=2 means purchase price, $retun_type=3 means disount, $retun_type=4 menas discount formatted
  {
    $ci = &get_instance();

    if($retun_type=='1')
    {
      if($sell_price>0 && ($sell_price<$original_price)) 
      {
        $return = "<span class='text-light' style='text-decoration:line-through'>".$currency_icon.number_format((float)$original_price, 2, '.', '')."</span> <span class='text-dark'>".$currency_icon.number_format((float)$sell_price, 2, '.', '')."</span>";
      }
      else $return = $currency_icon.number_format((float)$original_price, 2, '.', '');
    }
    else if($retun_type=='2')
    {
      if($sell_price>0 && ($sell_price<$original_price)) 
      {
        $return = $sell_price;
      }
      else $return = $original_price;
    }
    else
    {
      $disocunt = 0;
      if($sell_price>0 && ($sell_price<$original_price)) 
      {
        $disocunt = round((($original_price-$sell_price)/$original_price)*100);
        
        if($retun_type==4) $return = '<div class="yith-wcbsl-badge-wrapper yith-wcbsl-mini-badge"> <div class="yith-wcbsl-badge-content">'.$disocunt.'% OFF</div></div>';
        else $return = $disocunt;
      }
      else
      {
        if($retun_type==4) $return = '';
        else $return = 0;
      }

    }

    return $return;
  }
}

if ( ! function_exists('mec_attribute_map'))
{
  function mec_attribute_map($attribute_array=array(),$attribute_str='',$retun_type='string') // makes comma seperated attributes as name string (1,2 = Color,Size)
  {
    $explode = explode(',', $attribute_str);

    $output = array();
    foreach ($explode as $value) 
    {
      if(isset($attribute_array[$value])) $output[] = $attribute_array[$value];
    }
    if($retun_type=='string') return ucfirst(strtolower(implode(' , ', $output)));
    else return $output;
  }
}






