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
var mixerConfig = {
    load: {
        page: activePage
    },
    pagination: {
        limit: activeLimit
    },
    classNames: {
        block: 'mixitup',
        elementToggle: 'toggle'
    },
    layout: {
        allowNestedTargets: false
    },
    selectors: {
        target: '.prop-item',
        control: '.mixitup-control'
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
};


function getRange() {
    var min = Number(minPriceRangeInput.value);
    var max = Number(maxPriceRangeInput.value);
    var marketType = String(marketTypeInput.value);
    var bedroom = String(bedroomCountInput.value);
    var excludeSoldChecked = $('input[name="exclude_sold"]:checked').length == 1;

    return {
        min: min,
        max: max,
        type: marketType,
        bedroom: bedroom,
        excludeSold: excludeSoldChecked
    };
}

function filterTestResult(testResult, target) {
    var price = Number(target.dom.el.getAttribute('data-price'));
    var propType = String(target.dom.el.getAttribute('data-type'));
    var bedroom = Number(target.dom.el.getAttribute('data-bedroom'));
    var statusSold = Number(target.dom.el.getAttribute('data-status-sold'));
    var range = getRange();

    if(price>=range.min && price<=range.max && propType==range.type
        && (!range.excludeSold || (range.excludeSold && statusSold==0))
        && (range.bedroom=='' || (Number(range.bedroom)==bedroom && bedroom>=0 && bedroom<5) || (range.bedroom=='5' && bedroom>=5))) {
        return testResult;
    }

    return false;
}

function changeFltrOptions(id, selectValues, isFirstSelected){
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


$(document).ready(function(){
/*
    $('.category_icons.quickview1').each(function(){
        $(this).click(function(){
            setTimeout(function(){
                Webflow.ready();
            },500);
        });
    });
*/
    $('.w-condition-invisible.w-slide').remove();

    var mixerObj = mixitup('#mix-container', mixerConfig);

    function handleRangeInputChange() {
        mixerObj.filter(mixerObj.getState().activeFilter);
    }

    function handleMarketTypeInputChange() {
        var marketStatusType = String(marketTypeInput.value);

        if(marketStatusType == 'lettings'){
            $('#breadcrumb-market').html('LETTINGS');
            $('#breadcrumb-stoke').html('STOKE-ON-TRENT PROPERTY TO LET');
            $('#page-header').html('Properties for Rent in Stoke-on-Trent');
            $('#looking-property').html('LOOKING TO LET YOUR PROPERTY?');
            changeFltrOptions('minPrice', lettingPrices, true);
            changeFltrOptions('maxPrice', lettingPrices, false);
            $('#label-exclude_sold').html('Exclude Let Agreed Properties');
            $('#btn-book').attr('href', '/lettings-valuation');
        }else{
            if(marketStatusType == 'sales'){
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
            changeFltrOptions('minPrice', salesPrices, true);
            changeFltrOptions('maxPrice', salesPrices, false);
            $('#label-exclude_sold').html('Exclude Sold Properties');
            $('#btn-book').attr('href', '/sales-valuation');
        }

        handleRangeInputChange();
    }

    minPriceRangeInput.addEventListener('change', handleRangeInputChange);
    maxPriceRangeInput.addEventListener('change', handleRangeInputChange);
    bedroomCountInput.addEventListener('change', handleRangeInputChange);
    excludeSoldInput.addEventListener('change', handleRangeInputChange);
    marketTypeInput.addEventListener('change', handleMarketTypeInputChange);


    selectSort.addEventListener('change', function() {
        var order = selectSort.value;
        mixerObj.sort(order);
    });

    mixitup.Mixer.registerFilter('testResultEvaluateHideShow', 'range', filterTestResult);


    handleMarketTypeInputChange();

});

JS;

$this->registerJsFile(
//    '@web/js/mixitup.min.js', ['position' => $this::POS_HEAD]
    'https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.0/mixitup.min.js', ['position' => $this::POS_HEAD]
);

$this->registerJsFile(
    'https://dl.dropboxusercontent.com/s/s4jngiqvk1c9duf/mixitup-pagination.js', ['position' => $this::POS_HEAD]
);

$this->registerJsFile(
    'https://cdn.rawgit.com/malsup/cycle2/master/build/jquery.cycle2.min.js', ['position' => $this::POS_READY]
);

$this->registerJS($js, $this::POS_END);
?>
<div id="t" class="filter-section"><div class="form-block"><div class="form-block-6 w-form"><form id="email-form-2" name="email-form-2" data-name="Email Form 2" class="form-3"><select id="marketType" name="marketType" data-name="marketType" class="filter_select w-select"><option value="sales">To Buy</option><option value="lettings">For Rent</option><option value="auctions">Auctions</option></select><select id="minPrice" name="minPrice" data-name="minPrice" class="select-field w-select"><option value="0">Min Price</option><option value="50000">?50,000</option><option value="60000">?60,000</option><option value="70000">?70,000</option><option value="80000">?80,000</option><option value="90000">?90,000</option><option value="100000">?100,000</option><option value="110000">?110,000</option><option value="120000">?120,000</option><option value="125000">?125,000</option><option value="130000">?130,000</option><option value="140000">?140,000</option><option value="150000">?150,000</option><option value="160000">?160,000</option><option value="170000">?170,000</option><option value="175000">?175,000</option><option value="180000">?180,000</option><option value="190000">?190,000</option><option value="200000">?200,000</option><option value="210000">?210,000</option><option value="220000">?220,000</option><option value="230000">?230,000</option><option value="240000">?240,000</option><option value="250000">?250,000</option><option value="260000">?260,000</option><option value="270000">?270,000</option><option value="280000">?280,000</option><option value="290000">?290,000</option><option value="300000">?300,000</option><option value="325000">?325,000</option><option value="350000">?350,000</option><option value="375000">?375,000</option><option value="400000">?400,000</option><option value="425000">?425,000</option><option value="450000">?450,000</option><option value="475000">?475,000</option><option value="9000000">?500,000+</option></select><select id="maxPrice" name="maxPrice" data-name="maxPrice" class="select-field w-select"><option value="50000">?50,000</option><option value="60000">?60,000</option><option value="70000">?70,000</option><option value="80000">?80,000</option><option value="90000">?90,000</option><option value="100000">?100,000</option><option value="110000">?110,000</option><option value="120000">?120,000</option><option value="125000">?125,000</option><option value="130000">?130,000</option><option value="140000">?140,000</option><option value="150000">?150,000</option><option value="160000">?160,000</option><option value="170000">?170,000</option><option value="175000">?175,000</option><option value="180000">?180,000</option><option value="190000">?190,000</option><option value="200000">?200,000</option><option value="210000">?210,000</option><option value="220000">?220,000</option><option value="230000">?230,000</option><option value="240000">?240,000</option><option value="250000">?250,000</option><option value="260000">?260,000</option><option value="270000">?270,000</option><option value="280000">?280,000</option><option value="290000">?290,000</option><option value="300000">?300,000</option><option value="325000">?325,000</option><option value="350000">?350,000</option><option value="375000">?375,000</option><option value="400000">?400,000</option><option value="425000">?425,000</option><option value="450000">?450,000</option><option value="475000">?475,000</option><option value="9000000">?500,000+</option></select><select id="bedroomCount" name="bedroomCount" data-name="bedroomCount" class="select-field w-select"><option value="">All</option><option value="0">Studio</option><option value="1">1 Bed</option><option value="2">2 Bed</option><option value="3">3 Bed</option><option value="4">4 Bed</option><option value="5">5+ Bed</option></select></form><div class="w-form-done"><div>Thank you! Your submission has been received!</div></div><div class="w-form-fail"><div>Oops! Something went wrong while submitting the form.</div></div></div><div class="controls-form"><div class="filter_wrap"><div class="filter_check"><div data-delay="0" class="dropdown w-dropdown" role="menu"><div data-filter="all" class="dropdown-toggle w-dropdown-toggle mixitup-control-active" tabindex="0" aria-controls="w-dropdown-toggle-0" aria-haspopup="menu" style="outline: currentcolor none medium;"><div class="icon-4 w-icon-dropdown-toggle"></div><div class="text-block-27">To Buy</div></div><nav class="dropdown-list w-dropdown-list" id="w-dropdown-toggle-0"><a href="#" data-filter="all" class="dropdown-link-3 w-dropdown-link mixitup-control-active" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">All</a><a href="#" data-filter=".auctions" class="dropdown-link w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Auctions</a><a href="#" data-filter=".lettings" class="dropdown-link-2 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Lettings</a><a href="#" data-filter=".sales" class="dropdown-link-3 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Sales</a></nav></div></div><div class="filter_check"><div data-delay="0" class="dropdown w-dropdown" role="menu"><div class="dropdown-toggle w-dropdown-toggle" tabindex="0" aria-controls="w-dropdown-toggle-1" aria-haspopup="menu" style="outline: currentcolor none medium;"><div class="icon-4 w-icon-dropdown-toggle"></div><div class="text-block-27">from 100</div></div><nav class="dropdown-list w-dropdown-list" id="w-dropdown-toggle-1"><a href="#" class="dropdown-link w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Auctions</a><a href="#" class="dropdown-link-2 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Lettings</a><a href="#" class="dropdown-link-3 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Sales</a></nav></div></div><div class="filter_check"><div data-delay="0" class="dropdown w-dropdown" role="menu"><div class="dropdown-toggle w-dropdown-toggle" tabindex="0" aria-controls="w-dropdown-toggle-2" aria-haspopup="menu" style="outline: currentcolor none medium;"><div class="icon-4 w-icon-dropdown-toggle"></div><div class="text-block-27">up to 10000000</div></div><nav class="dropdown-list w-dropdown-list" id="w-dropdown-toggle-2"><a href="#" class="dropdown-link w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Auctions</a><a href="#" class="dropdown-link-2 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Lettings</a><a href="#" class="dropdown-link-3 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Sales</a></nav></div></div><div class="filter_check"><div data-delay="0" class="dropdown w-dropdown" role="menu"><div class="dropdown-toggle w-dropdown-toggle" tabindex="0" aria-controls="w-dropdown-toggle-3" aria-haspopup="menu" style="outline: currentcolor none medium;"><div class="icon-4 w-icon-dropdown-toggle"></div><div class="text-block-27">3+ bedrooms</div></div><nav class="dropdown-list w-dropdown-list" id="w-dropdown-toggle-3"><a href="#" class="dropdown-link w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Auctions</a><a href="#" class="dropdown-link-2 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Lettings</a><a href="#" class="dropdown-link-3 w-dropdown-link" tabindex="-1" role="menuitem" style="outline: currentcolor none medium;">Sales</a></nav></div></div></div></div></div></div>

<div class="block-bread-crumbs _2"><a href="/" class="breadcrumbs">HOME </a><div class="text-block-28"> &nbsp;&gt; &nbsp;</div><div id="breadcrumb-market" class="breadcrumbs">SALES</div><div class="text-block-28"> &nbsp;&gt; &nbsp;</div><div id="breadcrumb-stoke" class="breadcrumbs">STOKE-ON-TRENT PROPERTY FOR SALE</div></div>
<h1 id="page-header" class="h1 light _6">Properties for Rent in Stoke-on-Trent</h1>
<div class="block-sort"><div class="div-block-80"><div class="text-block-29">Showing <span class="blog-count">40</span> Properties</div></div><div class="div-block-82"><div class="sort-price w-form"><form id="email-form-3" name="email-form-3" data-name="Email Form 3" class="form-4"><select id="Order" name="Order" data-name="Order" class="sort_select w-select"><option value="price:desc">Highest Price First</option><option value="price:asc">Lowest Price First</option></select><label class="w-checkbox checkbox-field-3"><input type="checkbox" id="exclude_sold" name="exclude_sold" data-name="exclude_sold" class="w-checkbox-input checkbox"><span for="exclude_sold" id="label-exclude_sold" class="checkbox-label-3 w-form-label">Exclude Sold Properties</span></label></form><div class="w-form-done"><div>Thank you! Your submission has been received!</div></div><div class="w-form-fail"><div>Oops! Something went wrong while submitting the form.</div></div></div></div></div>


<?php echo $this->render('_search-result.php'); ?>

<div class="mixitup-page-list"></div>
<div class="mixitup-page-stats"></div>

