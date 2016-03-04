<?php
class incomeModel extends Model
{
    public function price_exists($price, $id = 0,$iid='') {

        $tot_price = $this->where("pid=".$id)->sum('price');
        $income_price = M('commission')->where("pid=".$id)->getField('income');
        $res = $income_price-$tot_price-$price;
        if($iid){
            $tot_price = $this->where("id !=$iid and  pid=".$id)->sum('price');
            $res = $income_price - ($tot_price+$price);
        }
        return  $res;
    }
}