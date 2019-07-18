<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 07.03.2019
 * Time: 10:35
 */

/* @var $this yii\web\View */

//use yii\web\View;


//<script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.0/mixitup.min.js"></script>
//<script src="https://dl.dropboxusercontent.com/s/sjmoui7gw650948/mixitup-pagination.js" ></script>

$js = <<< JS

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

var marketTypeParam = getUrlParameter('type');
if(marketTypeParam == 'sales' || marketTypeParam == 'lettings' || marketTypeParam == 'auctions'){
    $('#marketType').val(marketTypeParam);
}

var conv = function (str) {
    if (!str) {
        str = 'empty';
    }
    return str.replace(/[!\"#$%&'\(\)\*\+,\.\/:;<=>\?\@\[\\\]\^`\{\|\}~]/g, '')
        .replace(/ /g, "-")
        .toLowerCase()
        .trim();
};

var catArray = document.querySelectorAll('.w-dyn-item .categ');
catArray.forEach( function(elem) {
    var text = elem.innerText || elem.innerContent;
    var className = conv(text);
    if (!isNaN(parseInt(className.charAt(0), 10))) {
        className = ("_" + className);
    }
    elem.parentElement.classList.add(className);
    elem.parentElement.setAttribute('data-type', className);
});

var priceArray = document.querySelectorAll('.w-dyn-item .price-search');
priceArray.forEach( function(elem) {
    var price = elem.innerText || elem.innerContent;
    elem.parentElement.parentElement.parentElement.setAttribute('data-price', price);
});

var bedroomArray =  document.querySelectorAll('.w-dyn-item .bedroom-search');
bedroomArray.forEach( function(elem) {
    var bedroom = elem.innerText || elem.innerContent;
    if (typeof(bedroom) == "undefined") bedroom = '-1';

    elem.parentElement.setAttribute('data-bedroom', bedroom);
});

var marketStatusArray =  document.querySelectorAll('.w-dyn-item .market-status-search');
marketStatusArray.forEach( function(elem) {
    var status = elem.innerText || elem.innerContent;
    if (typeof(status) == "undefined") status = '1';
    if (status=='For Sale' || status=='To Let') status='0'
    else status='1';

    elem.parentElement.setAttribute('data-status-sold', status);
});

var minPriceTxt = "Min Price";
var containerEl = $('#mix-container');
var minPriceRangeInput = document.querySelector('[name="minPrice"]');
var maxPriceRangeInput = document.querySelector('[name="maxPrice"]');
var marketTypeInput = document.querySelector('[name="marketType"]');
var bedroomCountInput = document.querySelector('[name="bedroomCount"]');
var excludeSoldInput = document.querySelector('[name="exclude_sold"]');
var lettingPrices = {"0":minPriceTxt,"100":"100","150":"150","200":"200","250":"250","300":"300","350":"350","400":"400","450":"450","500":"500","600":"600","700":"700","800":"800","900":"900","100000":"1,000+"};
var salesPrices = {"0":minPriceTxt,"50000":"50,000","60000":"60,000","70000":"70,000","80000":"80,000","90000":"90,000","100000":"100,000","110000":"110,000","120000":"120,000","125000":"125,000","130000":"130,000","140000":"140,000","150000":"150,000","160000":"160,000","170000":"170,000","175000":"175,000","180000":"180,000","190000":"190,000","200000":"200,000","210000":"210,000","220000":"220,000","230000":"230,000","240000":"240,000","250000":"250,000","260000":"260,000","270000":"270,000","280000":"280,000","290000":"290,000","300000":"300,000","325000":"325,000","350000":"350,000","375000":"375,000","400000":"400,000","425000":"425,000","450000":"450,000","475000":"475,000","9000000":"500,000+"};
var activePage = 1;
var activeLimit = 8;
var selectSort = document.querySelector('.sort_select');
var mixer = mixitup(containerEl, {
    load: {
        page: activePage
    },
    pagination: {
        limit: activeLimit
    },
    callbacks: {
        onMixEnd: function(state) {
            $('.blog-count').text(state.totalMatching);

/*            Webflow.require('slider').redraw();*/

            if (state.activePagination.limit === activeLimit && state.activePagination.page === activePage) return;

            activePage = state.activePagination.page;
            activeLimit = state.activePagination.limit;

            $("body,html").animate({
                scrollTop: $("#page-header").offset().top
            }, 800);
        }
    }
});

function getRange() {
    var min = Number(minPriceRangeInput.value);
    var max = Number(maxPriceRangeInput.value);
    var type = String(marketTypeInput.value);
    var bedroom = String(bedroomCountInput.value);
    var excludeSoldChecked = $('input[name="exclude_sold"]:checked').length == 1;

    return {
        min: min,
        max: max,
        type: type,
        bedroom: bedroom,
        excludeSold: excludeSoldChecked
    };
}

function handleRangeInputChange() {
    mixer.filter(mixer.getState().activeFilter);
}

function handleMarketTypeInputChange() {
    var type = String(marketTypeInput.value);

    if(type == 'lettings'){
        $('#breadcrumb-market').html('LETTINGS');
        $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY TO LET');
        $('#page-header').html('Properties for Rent in Stoke-on-Trent');
        $('#looking-property').html('LOOKING TO LET YOUR PROPERTY?');
        changeOptions('minPrice', lettingPrices, true);
        changeOptions('maxPrice', lettingPrices, false);
        $('#label-exclude_sold').html('Exclude Let Agreed Properties');
        $('#btn-book').attr('href', '/lettings-valuation');
    }else{
        if(type == 'sales'){
            $('#breadcrumb-market').html('SALES');
            $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY FOR SALE');
            $('#page-header').html('Properties for Sale in Stoke-on-Trent');
            $('#looking-property').html('LOOKING TO SELL YOUR PROPERTY?');
        }else{
            $('#breadcrumb-market').html('AUCTIONS');
            $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY FOR AUCTION');
            $('#page-header').html('Properties for Auction in Stoke-on-Trent');
            $('#looking-property').html('LOOKING TO SELL YOUR PROPERTY?');
        }
        changeOptions('minPrice', salesPrices, true);
        changeOptions('maxPrice', salesPrices, false);
        $('#label-exclude_sold').html('Exclude Sold Properties');
        $('#btn-book').attr('href', '/sales-valuation');
    }

    handleRangeInputChange();
}

function changeOptions(id, selectValues, isFirstSelected){
    $('#'+id).find('option').remove();
    $.each(selectValues, function(key, value) {
        if(!(!isFirstSelected && key=='0')){
            value = key == '0' ? value : "&pound;" + value;
            $('#' + id).append($('<option>', {value: key}).html(value));
        }
    });

    if(isFirstSelected)
        $('#'+id).val($('#'+id+' option:first').val());
    else
        $('#'+id).val($('#'+id+' option:last').val());
}

function filterTestResult(testResult, target) {
    var price = Number(target.dom.el.getAttribute('data-price'));
    var type = String(target.dom.el.getAttribute('data-type'));
    var bedroom = Number(target.dom.el.getAttribute('data-bedroom'));
    var statusSold = Number(target.dom.el.getAttribute('data-status-sold'));
    var range = getRange();

    if(price>=range.min && price<=range.max && type==range.type
        && (!range.excludeSold || (range.excludeSold && statusSold==0))
        && (range.bedroom=='' || (Number(range.bedroom)==bedroom && bedroom>=0 && bedroom<5) || (range.bedroom=='5' && bedroom>=5))) {
        return testResult;
    }

    return false;
}

mixitup.Mixer.registerFilter('testResultEvaluateHideShow', 'range', filterTestResult);
minPriceRangeInput.addEventListener('change', handleRangeInputChange);
maxPriceRangeInput.addEventListener('change', handleRangeInputChange);
bedroomCountInput.addEventListener('change', handleRangeInputChange);
excludeSoldInput.addEventListener('change', handleRangeInputChange);
marketTypeInput.addEventListener('change', handleMarketTypeInputChange);

handleMarketTypeInputChange();

selectSort.addEventListener('change', function() {
    var order = selectSort.value;
    mixer.sort(order);
});

$(document).ready(function(){
    $('.category_icons.quickview1').each(function(){
        $(this).click(function(){
            setTimeout(function(){
//                Webflow.ready();
            },500);
        });
    });

    $('.w-condition-invisible.w-slide').remove();
});

JS;

$this->registerJsFile(
//    '@web/js/mixitup.min.js', ['position' => $this::POS_HEAD]
    'https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.0/mixitup.min.js', ['position' => $this::POS_HEAD]
);

$this->registerJsFile(
    'https://dl.dropboxusercontent.com/s/s4jngiqvk1c9duf/mixitup-pagination.js', ['position' => $this::POS_HEAD]
);
$this->registerJS($js, $this::POS_READY);
?>

<div id="page-header" class="row">Showing <span class="blog-count">0</span> Properties</div>

<form class="form-inline">
    <div class="form-group">
        <label class="range-slider-label">Type</label>
        <select id="marketType" name="marketType" data-name="marketType" class="form-control">
            <option value="sales">Sales</option>
            <option value="lettings">Lettings</option>
            <option value="auctions">Auction</option>
        </select>
    </div>

    <div class="form-group">
        <label class="range-slider-label">Price</label>
        <select id="minPrice" name="minPrice" data-name="minPrice" class="form-control">
        </select>
    </div>

    <div class="form-group">
        <label class="range-slider-label"> - </label>
        <select id="maxPrice" name="maxPrice" data-name="maxPrice" class="form-control">
        </select>
    </div>

    <div class="form-group">
        <label class="range-slider-label">Bedrooms</label>
        <select id="bedroomCount" name="bedroomCount" data-name="bedroomCount" class="form-control">
            <option value="">All</option>
            <option value="0">Studio</option>
            <option value="1">1 Bed</option>
            <option value="2">2 Bed</option>
            <option value="3">3 Bed</option>
            <option value="4">4 Bed</option>
            <option value="5">5+ Bed</option>
        </select>

    </div>
</form>

<select id="Order" name="Order" data-name="Order" class="sort_select w-select"><option value="price:desc">High price first</option><option value="price:asc">Lowest Price First</option></select>
<div class="form-group">
    <label class="w-checkbox checkbox-field-3"><input type="checkbox" id="exclude_sold" name="exclude_sold" data-name="exclude_sold" class="w-checkbox-input checkbox"><span for="exclude_sold" id="label-exclude_sold" class="checkbox-label-3 w-form-label">Exclude Sold Properties</span></label>
</div>

<div id="mix-container" class="row container" data-ref="container">
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">4</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">330000</div></div></div><div class="bedroom-search">3</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">420000</div></div></div><div class="bedroom-search">2</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">575000</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">370000</div></div></div><div class="bedroom-search">4</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">865000</div></div></div><div class="bedroom-search">3</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">220000</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">1020000</div></div></div><div class="bedroom-search">2</div><div class="market-status-search">Sold</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">750000</div></div></div><div class="bedroom-search">4</div><div class="market-status-search">Sold</div></div>


    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">4</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">330000</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">For Sale</div></div>
    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">420000</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">Sold</div></div>

    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">1200</div></div></div><div class="bedroom-search">4</div><div class="market-status-search">To Let</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">2300</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">To Let</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">500</div></div></div><div class="bedroom-search">2</div><div class="market-status-search">To Let</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">1500</div></div></div><div class="bedroom-search">2</div><div class="market-status-search">To Let</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">5200</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">Let</div></div>
    <div class="mix w-dyn-item"><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">7500</div></div></div><div class="bedroom-search">1</div><div class="market-status-search">Let</div></div>
</div>

<div class="mixitup-page-list"></div>
<div class="mixitup-page-stats"></div>

