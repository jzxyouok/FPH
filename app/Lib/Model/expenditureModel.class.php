<?php
class expenditureModel extends Model
{
    public function price_exists($price, $id = 0,$iid='') {
        
        $tot_price = $this->where("pid=".$id)->sum('price');
        $expenditure_price = M('commission')->where("pid=".$id)->getField('expenditure');
        $res = $expenditure_price-$tot_price-$price;
        if($iid){
            $tot_price = $this->where("id !=$iid and  pid=".$id)->sum('price');
            $res = $expenditure_price - ($tot_price+$price);
        }
        return $res;
    }

}
