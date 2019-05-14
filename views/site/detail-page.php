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
    submitBtnClass: "button-violet w-button dpt_modal-button",
    resultElementId: "prefered_time_date-input"
});

loadSimilar();

$('.carousel-property').not('.item-updated').remove();

function loadSimilar(){
    var itemsFilled = 0;
    var curPropertyRole = $('#current-prop-role-type').html();
    var curPropertyType = $('#current-prop-type').html();
    var curPropertyBeds = Number($('#current-prop-beds').html());
    var currentPropertyId = $('#current-prop-id').html();

    $('.property-item').each(function(){
        var itemPropertyId = $(this).find('div.item-property_id').html();
        var itemRoleType = $(this).find('div.item-role_type').html();
        var itemPropertyStatus = $(this).find('div.item-prop_status').html();
        var itemPropertyBeds = Number($(this).find('div.item-prop_beds').html());
        var itemPropertyImage = $(this).find('a');

        if(itemRoleType==curPropertyRole
            && itemPropertyStatus==curPropertyType
            && itemPropertyId!=currentPropertyId
            && curPropertyBeds-1<=itemPropertyBeds && curPropertyBeds+1>=itemPropertyBeds
            ){

            var carouselDiv = $('.carousel-property')[itemsFilled];
            var carouselItem = $('.collection-list-wrapper-36').find(carouselDiv);
            var carouselImage = carouselItem.find('a.text-prop-image');
            carouselImage.attr('href', itemPropertyImage.attr('href'));
            carouselImage.attr('style', itemPropertyImage.attr('style'));
            carouselItem.find('h2.text-prop-name').html(itemPropertyImage.html());
            carouselItem.find('div.text-price-value').html($(this).find('div.item-prop-price').html());
            carouselItem.find('div.text-prop-address').html($(this).find('div.item-prop-address').html());
            carouselItem.addClass('item-updated');
            itemsFilled++;
        }
    });
}

JS;

$this->registerCssFile('@web/css/webflow.css', ['position' => $this::POS_HEAD]);
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
        <input id="prefered_time_date-input" type="hidden" value="" />
    </div>
</form>


<div class="block-content-property _2"><div class="blockbrief-information"><h1 class="h1 property">2 bed terraced house for sale</h1><div class="text-address-property">Heath Street, Stoke-on-Trent, Goldenhill, Staffordshire ST6 5RZ</div><div class="block-price-property"><div class="text-price-property-t">Guide Price &pound;</div><div class="text-block-75">75,000</div><div class="text-price-property">75000</div></div><a href="#" class="button-violet m w-button" data-ix="open-order-form">arrange a viewing</a><div class="div-block-115">
            <div id="current-prop-id">14653833</div>
            <div id="current-prop-role-type">Sales</div><div id="current-prop-type">For Sale</div>
            <div id="current-prop-beds">3</div></div></div><div class="block-kontakt _2"><div class="text-block-37">Local office</div><div class="text-block-38">Why not visit us in person at our local Stoke on Trent office?</div><div class="text-block-39"><a href="tel:+44(0)1782970222" class="link-31">+44 (0)1782 970222</a></div><a href="mailto:hello@oneagencygroup.co.uk" class="button-10 w-button">Email Office Directly</a><div class="text-block-41"><a href="/contact" class="link-6">View Office &nbsp;Details</a></div></div><div class="categ">sales</div></div>

<div class="collection-list-wrapper-44 w-dyn-list">
    <div class="property-list w-dyn-items">
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c591c3d7e44b57af79_12534193.jpeg')" href="/property-listings/14637004">1 bed semi-detached house for sale</a>
            <div class="item-property_id">14637004</div>
            <div class="item-role_type">Sales</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">1</div>
            <div class="item-prop-price">140,000</div>
            <div class="item-prop-address">Kemball Avenue, Stoke-on-Trent, Mount Pleasant, Staffordshire ST4 4LD</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c00208a6152a3ee9cc_12544900.jpeg')" href="/property-listings/14662804">3 bed terraced house for sale</a>
            <div class="item-property_id">14662804</div>
            <div class="item-role_type">Lettings</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">3</div>
            <div class="item-prop-price">115,000</div>
            <div class="item-prop-address">Highton Street, Stoke-on-Trent, Milton, Staffordshire ST2 7BA</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919bdc4df33b387fa5356_12505734.jpeg')" href="/property-listings/14643502">3 bed town house for sale</a>
            <div class="item-property_id">14643502</div>
            <div class="item-role_type">Sales</div>
            <div class="item-prop_status">SSTC</div>
            <div class="item-prop_beds">3</div>
            <div class="item-prop-price">115,000</div>
            <div class="item-prop-address">Leek Road, Stoke-on-Trent, Hanley, Staffordshire ST1 3NQ</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919b6880c3916d9c390f9_12510828.jpeg')" href="/property-listings/14597934">3 bed semi-detached house for sale</a>
            <div class="item-property_id">14597934</div><div class="item-role_type">Sales</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">3</div>
            <div class="item-prop-price">100,000</div>
            <div class="item-prop-address">Dividy Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9JW</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919ad390fe891cbe6010b_12510912.jpeg')" href="/property-listings/14647909">3 bed terraced house for sale</a>
            <div class="item-property_id">14647909</div>
            <div class="item-role_type">Sales</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">3</div>
            <div class="item-prop-price">90,000</div>
            <div class="item-prop-address">Werrington Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9AB</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919a913825af473a4d9b0_12484256.jpeg')" href="/property-listings/14640901">2 bed terraced house for sale</a>
            <div class="item-property_id">14640901</div>
            <div class="item-role_type">Sales</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">2</div>
            <div class="item-prop-price">80,000</div>
            <div class="item-prop-address">Buxton Street, Stoke-on-Trent, Sneyd Green, Staffordshire ST1 6BN</div>
        </div>
        <div class="property-item w-dyn-item">
            <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919a44700a218dd0d8566_12534192.jpeg')" href="/property-listings/14653833" class="w--current">2 bed terraced house for sale</a>
            <div class="item-property_id">14653833</div>
            <div class="item-role_type">Sales</div>
            <div class="item-prop_status">For Sale</div>
            <div class="item-prop_beds">2</div>
            <div class="item-prop-price">75,000</div>
            <div class="item-prop-address">Heath Street, Stoke-on-Trent, Goldenhill, Staffordshire ST6 5RZ</div>
        </div></div></div>

<div class="similar-section">
    <div>
        <div class="div-block-81">
            <div data-animation="slide" data-easing="linear" data-duration="500" data-infinite="1" class="slider-news _23 w-slider">
                <div class="div-block-31 _222"><h2 class="heading-7 _23">Similar properties for sale in Stoke on Trent</h2><div class="text-light-18 _12">Discover your next home in Stoke-on-Trent with OneAgency. </div></div>
                <div class="mask w-slider-mask">
                    <div class="slide-1 _21 w-slide" style="transform: translateX(0px); opacity: 1;">
                        <div class="collection-list-wrapper-36 w-dyn-list">
                            <div class="collection-list-3 w-dyn-items">
                                <div class="collection-item-10 _211 carousel-property w-dyn-item">
                                    <div class="block-b"></div>
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c591c3d7e44b57af79_12534193.jpeg')" href="/property-listings/14637004" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <div class="no-image _2">No image</div>
                                    <h2 class="heading-6 text-prop-name">2 bed semi-detached house for sale</h2>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Kemball Avenue, Stoke-on-Trent, Mount Pleasant, Staffordshire ST4 4LD</div>
                                    </div>
                                    <div class="block-text-price _2">
                                        <div class="text-price2">Guide Price &pound;</div>
                                        <div class="text-price text-price-sign">&pound;</div>
                                        <div class="text-block-64 text-price-value">140,000</div>
                                    </div>
                                </div>
                                <div class="collection-item-10 _211 carousel-property w-dyn-item">
                                    <div class="block-b"></div>
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c00208a6152a3ee9cc_12544900.jpeg')" href="/property-listings/14662804" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <div class="no-image _2">No image</div>
                                    <h2 class="heading-6 text-prop-name">3 bed terraced house for sale</h2>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Highton Street, Stoke-on-Trent, Milton, Staffordshire ST2 7BA</div>
                                    </div>
                                    <div class="block-text-price _2">
                                        <div class="text-price2">Guide Price &pound;</div>
                                        <div class="text-price text-price-sign">&pound;</div>
                                        <div class="text-block-64 text-price-value">115,000</div>
                                    </div>
                                </div>
                                <div class="collection-item-10 _211 carousel-property w-dyn-item">
                                    <div class="block-b"></div>
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919bdc4df33b387fa5356_12505734.jpeg')" href="/property-listings/14643502" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <div class="no-image _2">No image</div>
                                    <h2 class="heading-6 text-prop-name">3 bed town house for sale</h2>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Leek Road, Stoke-on-Trent, Hanley, Staffordshire ST1 3NQ</div>
                                    </div>
                                    <div class="block-text-price _2">
                                        <div class="text-price2">Guide Price &pound;</div>
                                        <div class="text-price text-price-sign">&pound;</div><div class="text-block-64 text-price-value">115,000</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="slide-5 w-slide" style="transform: translateX(0px); opacity: 1;">
                        <div class="collection-list-wrapper-36 w-dyn-list">
                            <div class="collection-list-3 w-dyn-items">
                                <div class="collection-item-10 carousel-property w-dyn-item">
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919bdc4df33b387fa5356_12505734.jpeg')" href="/property-listings/14643502" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <a href="#" class="link-block-10 w-inline-block">
                                        <h2 class="heading-6 heading-17 text-prop-name">3 bed town house for sale</h2>
                                    </a>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Leek Road, Stoke-on-Trent, Hanley, Staffordshire ST1 3NQ</div>
                                    </div>
                                    <div class="block-text-price _2">
                                        <div class="text-price text-price-sign">&pound;</div>
                                        <div class="text-block-64 text-price-value">115,000</div>
                                        <div class="text-price2">Guide Price &pound;</div>
                                    </div>
                                </div>
                                <div class="collection-item-10 carousel-property w-dyn-item">
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919b6880c3916d9c390f9_12510828.jpeg')" href="/property-listings/14597934" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <a href="#" class="link-block-10 w-inline-block">
                                        <h2 class="heading-6 heading-17 text-prop-name">3 bed semi-detached house for sale</h2>
                                    </a>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Dividy Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9JW</div>
                                    </div>
                                    <div class="block-text-price _2"><div class="text-price text-price-sign">&pound;</div>
                                        <div class="text-block-64 text-price-value">100,000</div>
                                        <div class="text-price2">Guide Price &pound;</div>
                                    </div>
                                </div>
                                <div class="collection-item-10 carousel-property w-dyn-item">
                                    <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919ad390fe891cbe6010b_12510912.jpeg')" href="/property-listings/14647909" class="link-block-6 text-prop-image w-inline-block"></a>
                                    <a href="#" class="link-block-10 w-inline-block">
                                        <h2 class="heading-6 heading-17 text-prop-name">3 bed terraced house for sale</h2>
                                    </a>
                                    <div class="block-text-address">
                                        <div class="text-light-18 _2 text-prop-address">Werrington Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9AB</div>
                                    </div>
                                    <div class="block-text-price _2">
                                        <div class="text-price text-price-sign">&pound;</div>
                                        <div class="text-block-64 text-price-value">90,000</div>
                                        <div class="text-price2">Guide Price &pound;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="left-arrow-4 mo w-slider-arrow-left"></div>
                <img src="https://assets.website-files.com/5c08de022862dd501744f96f/5c706bd1fd0819deeb8bd19f_left%20arrow.png" alt="" class="image-7">
                <div class="right-arrow-4 mo w-slider-arrow-right"></div>
                <div class="slide-nav-3 w-slider-nav w-round">
                    <div class="w-slider-dot w-active" data-wf-ignore=""></div>
                    <div class="w-slider-dot" data-wf-ignore=""></div>
                </div>
            </div>
        </div>
    </div>
    <div class="mobi-slider">
        <div data-animation="slide" data-duration="500" data-infinite="1" class="slider-28 w-slider">
            <div class="div-block-31 _222 _54 _3">
                <h2 class="heading-7 _23">Similar properties for sale in Stoke on Trent</h2>
                <div class="text-light-18 _12">Discover your next home in Stoke-on-Trent with OneAgency. </div>
            </div>
            <div class="mask-4 w-slider-mask">
                <div class="sl1 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c591c3d7e44b57af79_12534193.jpeg')" href="/property-listings/14637004" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">2 bed semi-detached house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Kemball Avenue, Stoke-on-Trent, Mount Pleasant, Staffordshire ST4 4LD</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">140,000</div>
                                    <div class="price-search">140000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sl2 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919c00208a6152a3ee9cc_12544900.jpeg')" href="/property-listings/14662804" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">3 bed terraced house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Highton Street, Stoke-on-Trent, Milton, Staffordshire ST2 7BA</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">115,000</div>
                                    <div class="price-search">115000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sl3 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919bdc4df33b387fa5356_12505734.jpeg')" href="/property-listings/14643502" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">3 bed town house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Leek Road, Stoke-on-Trent, Hanley, Staffordshire ST1 3NQ</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">115,000</div>
                                    <div class="price-search">115000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sl4 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919b6880c3916d9c390f9_12510828.jpeg')" href="/property-listings/14597934" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">3 bed semi-detached house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Dividy Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9JW</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">100,000</div>
                                    <div class="price-search">100000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sl5 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919ad390fe891cbe6010b_12510912.jpeg')" href="/property-listings/14647909" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">3 bed terraced house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Werrington Road, Stoke-on-Trent, Bucknall, Staffordshire ST2 9AB</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">90,000</div>
                                    <div class="price-search">90000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sl6 w-slide">
                    <div class="collection-list-wrapper-36 mobi w-dyn-list">
                        <div class="collection-list-3 w-dyn-items">
                            <div class="collection-item-10 _211 w-dyn-item">
                                <div class="block-b"></div>
                                <a style="background-image:url('https://assets.website-files.com/5c08e1d2a482fee7e3b0dfa9/5cd919a913825af473a4d9b0_12484256.jpeg')" href="/property-listings/14640901" class="link-block-6 w-inline-block"></a>
                                <div class="no-image _2">No image</div>
                                <h2 class="heading-6">2 bed terraced house for sale</h2>
                                <div class="block-text-address">
                                    <div class="text-light-18 _2">Buxton Street, Stoke-on-Trent, Sneyd Green, Staffordshire ST1 6BN</div>
                                </div>
                                <div class="block-text-price _2">
                                    <div class="text-price2">Guide Price &pound;</div>
                                    <div class="text-price">&pound;</div>
                                    <div class="text-block-64">80,000</div>
                                    <div class="price-search">80000.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="left-arrow-4 w-slider-arrow-left"></div>
            <div class="right-arrow-4 _7777 w-slider-arrow-right"></div>
            <div class="slide-nav-6 w-slider-nav w-round"></div>
        </div>
    </div>
</div>


