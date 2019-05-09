<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 07.03.2019
 * Time: 10:35
 */

/* @var $this yii\web\View */

//use yii\web\View;



$js = <<< JS

var marketType = $(".categ").html();

if(marketType == 'lettings'){
    $('#breadcrumb-market').html('LETTINGS');
    $('#breadcrumb-market').attr("href", $('#breadcrumb-market').attr("href")+"?type=lettings");
    $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY TO LET');
    $('#page-header').html('Properties for Rent in Stoke-on-Trent');
}else{
    if(marketType == 'sales'){
        $('#breadcrumb-market').html('SALES');
        $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY FOR SALE');
        $('#page-header').html('Properties for Sale in Stoke-on-Trent');
    }else{
        $('#breadcrumb-market').html('AUCTIONS');
        $('#breadcrumb-market').attr("href", $('#breadcrumb-market').attr("href")+"?type=auctions");
        $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY FOR AUCTION');
        $('#page-header').html('Properties for Auction in Stoke-on-Trent');
    }
}

$('#prefered-time-date').addClass('dtp_main');
$('#prefered-time-date').dateTimePicker({
    dateFormat: "DD/MM/YYYY HH:mm",
    title: "Prefered Time & Date",
    mainClass: "text-field-2 w-input",
    submitBtnClass: "button-violet w-button dpt_modal-button"
});
JS;

$this->registerCssFile('@web/css/datetimepicker.css', ['position' => $this::POS_HEAD]);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', ['position' => $this::POS_HEAD]);


$this->registerJsFile('@web/js/moment-with-locales.min.js', ['position' => $this::POS_HEAD]);

$this->registerJsFile(
    '@web/js/datetimepicker.js', [
        'position' => $this::POS_HEAD,
        'depends' => [\yii\web\YiiAsset::className()]
    ]
);
$this->registerJS($js, $this::POS_READY);
?>

<div class="row">Showing <span class="blog-count">0</span> Properties</div>

<form>
    <div class="form-group">
        <label class="range-slider-label">Type</label>
        <select id="marketType" name="marketType" class="form-control">
            <option value="sales">Sales</option>
            <option value="lettings">Lettings</option>
            <option value="auctions">Auction</option>
        </select>
    </div>

    <div class="form-group">
        <label class="range-slider-label">Price</label>
        <select name="minPrice" class="form-control">
        </select>
    </div>

    <div class="form-group">
        <label class="range-slider-label"> - </label>
        <select name="maxPrice" class="form-control">
        </select>
    </div>

    <div class="form-group">
        <div id="prefered-time-date">Prefered time and sate</div>
        <input type="hidden" value="" />
    </div>
</form>

