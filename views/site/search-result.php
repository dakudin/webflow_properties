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

    // retrieve get parameters by its name
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

    // Reusable function to convert any string/text to css-friendly format
    var conv = function (str) {
    if (!str) {
        str = 'empty';
        }
    return str.replace(/[!\"#$%&'\(\)\*\+,\.\/:;<=>\?\@\[\\\]\^`\{\|\}~]/g, '')
              .replace(/ /g, "-")
              .toLowerCase()
              .trim();
    };

    // Creating dynamic elements classes from its categories:
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

    // Creating a custom data-price attributes from property prices:
    var priceArray = document.querySelectorAll('.w-dyn-item .price-search');
    priceArray.forEach( function(elem) {
        var price = elem.innerText || elem.innerContent;
        elem.parentElement.parentElement.parentElement.setAttribute('data-price', price);
    });

    // Creating a custom data-bedroom attributes from property bedrooms:
    var bedroomArray =  document.querySelectorAll('.w-dyn-item .bedroom-search');
    bedroomArray.forEach( function(elem) {
        var bedroom = elem.innerText || elem.innerContent;
        elem.parentElement.setAttribute('data-bedroom', bedroom);
    });

    //  var containerEl = document.querySelector('.container');
    var containerEl = document.querySelector('[data-ref="container"');
    var minPriceRangeInput = document.querySelector('[name="minPrice"]');
    var maxPriceRangeInput = document.querySelector('[name="maxPrice"]');
    var marketTypeInput = document.querySelector('[name="marketType"]');
    var beddromCountInput = document.querySelector('[name="beddromCount"]');

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

                if (state.activePagination.limit === activeLimit && state.activePagination.page === activePage) return;

                // Pagination state has changed:
                activePage = state.activePagination.page;
                activeLimit = state.activePagination.limit;
            }
        }
    });

    function getRange() {
        var min = Number(minPriceRangeInput.value);
        var max = Number(maxPriceRangeInput.value);
        var type = String(marketTypeInput.value);
		var bedroom = Number(beddromCountInput.value);

        return {
            min: min,
            max: max,
            type: type,
			bedroom: bedroom
        };
    }

    function handleRangeInputChange() {
        mixer.filter(mixer.getState().activeFilter);
    }

    function handleMarketTypeInputChange() {
        var type = String(marketTypeInput.value);

        if(type == 'lettings'){
            minPriceRangeInput.innerHTML = getLettingsOptions(true).join();
            maxPriceRangeInput.innerHTML = getLettingsOptions(false).join();
        }else{
            minPriceRangeInput.innerHTML = getSalesOptions(true).join();
            maxPriceRangeInput.innerHTML = getSalesOptions(false).join();
        }

        handleRangeInputChange();
    }

    function getLettingsOptions(firstSelected){
        var arrOptions = [];

        if(firstSelected)
            arrOptions.push('<option value="100" selected>&pound;100</option>');
        else
            arrOptions.push('<option value="100">&pound;100</option>');

        arrOptions.push('<option value="200">&pound;200</option>');
        arrOptions.push('<option value="300">&pound;300</option>');
        arrOptions.push('<option value="400">&pound;400</option>');
        arrOptions.push('<option value="500">&pound;500</option>');
        arrOptions.push('<option value="700">&pound;700</option>');
        arrOptions.push('<option value="1000">&pound;1,000</option>');
        arrOptions.push('<option value="1200">&pound;1,200</option>');
        arrOptions.push('<option value="1500">&pound;1,500</option>');
        arrOptions.push('<option value="2000">&pound;2,000</option>');
        arrOptions.push('<option value="2500">&pound;2,500</option>');
        arrOptions.push('<option value="3000">&pound;3,000</option>');
        arrOptions.push('<option value="5000">&pound;5,000</option>');
        arrOptions.push('<option value="7000">&pound;7,000</option>');
        arrOptions.push('<option value="10000">&pound;10,000</option>');

        if(firstSelected)
            arrOptions.push('<option value="49000">&pound;15,000+</option>');
        else
            arrOptions.push('<option value="49000" selected>&pound;15,000+</option>');

        return arrOptions;
    }

    function getSalesOptions(firstSelected){
        var arrOptions = [];

        if(firstSelected)
            arrOptions.push('<option value="50000" selected>&pound;50000</option>');
        else
            arrOptions.push('<option value="50000">&pound;50000</option>');

        arrOptions.push('<option value="100000">&pound;100,000</option>');
        arrOptions.push('<option value="150000">&pound;150,000</option>');
        arrOptions.push('<option value="200000">&pound;200,000</option>');
        arrOptions.push('<option value="300000">&pound;300,000</option>');
        arrOptions.push('<option value="400000">&pound;400,000</option>');
        arrOptions.push('<option value="500000">&pound;500,000</option>');
        arrOptions.push('<option value="600000">&pound;600,000</option>');
        arrOptions.push('<option value="700000">&pound;700,000</option>');
        arrOptions.push('<option value="800000">&pound;800,000</option>');
        arrOptions.push('<option value="1000000">&pound;1,000,000</option>');
        arrOptions.push('<option value="1500000">&pound;1,500,000</option>');
        arrOptions.push('<option value="2000000">&pound;2,000,000</option>');
        arrOptions.push('<option value="2500000">&pound;2,500,000</option>');

        if(firstSelected)
            arrOptions.push('<option value="9000000">&pound;3,000,000+</option>');
        else
            arrOptions.push('<option value="9000000" selected>&pound;3,000,000+</option>');

        return arrOptions;
    }

    function filterTestResult(testResult, target) {
        var price = Number(target.dom.el.getAttribute('data-price'));
        var type = String(target.dom.el.getAttribute('data-type'));
        var bedroom = Number(target.dom.el.getAttribute('data-bedroom'));
        var range = getRange();

        if (price >= range.min && price <= range.max && type == range.type
			&& (range.bedroom == 0 || (range.bedroom == bedroom && bedroom > 0 && bedroom < 3) || (range.bedroom == 3 && bedroom >= 3))) {
            return testResult;
        }

        return false;
    }

    mixitup.Mixer.registerFilter('testResultEvaluateHideShow', 'range', filterTestResult);
    minPriceRangeInput.addEventListener('change', handleRangeInputChange);
    maxPriceRangeInput.addEventListener('change', handleRangeInputChange);
    beddromCountInput.addEventListener('change', handleRangeInputChange);
    marketTypeInput.addEventListener('change', handleMarketTypeInputChange);

    handleMarketTypeInputChange();

    selectSort.addEventListener('change', function() {
        var order = selectSort.value;
        mixer.sort(order);
    });

JS;

$this->registerJsFile(
    '@web/js/mixitup.min.js', ['position' => $this::POS_HEAD]
);

$this->registerJsFile(
    'https://dl.dropboxusercontent.com/s/sjmoui7gw650948/mixitup-pagination.js', ['position' => $this::POS_HEAD]
);
$this->registerJS($js, $this::POS_READY);
?>

<div class="row">Showing <span class="blog-count">0</span> Properties</div>

<form class="form-inline">
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
        <label class="range-slider-label">Bedrooms</label>
        <select id="beddromCount" name="beddromCount" data-name="beddromCount" class="form-control">
            <option value="0">All</option><option value="1">1 Bedroom</option>
            <option value="2">2 Bedrooms</option>
            <option value="3">3+ Bedrooms</option>
        </select>
    </div>
</form>

<select id="Order" name="Order" data-name="Order" class="sort_select w-select"><option value="price:desc">High price first</option><option value="price:asc">Lowest Price First</option></select>

<div class="row container" data-ref="container">
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">4</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">330000</div></div></div><div class="bedroom-search">3</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">420000</div></div></div><div class="bedroom-search">2</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">575000</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">370000</div></div></div><div class="bedroom-search">4</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">865000</div></div></div><div class="bedroom-search">3</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">220000</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">1020000</div></div></div><div class="bedroom-search">2</div></div>
    <div class="mix w-dyn-item" ><div class="categ">sales</div><div><div>price &pound;<div class="price-search">750000</div></div></div><div class="bedroom-search">4</div></div>


    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">120000</div></div></div><div class="bedroom-search">4</div></div>
    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">330000</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item" ><div class="categ">auctions</div><div><div>price &pound;<div class="price-search">420000</div></div></div><div class="bedroom-search">1</div></div>

    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">1200</div></div></div><div class="bedroom-search">4</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">2300</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">500</div></div></div><div class="bedroom-search">2</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">1500</div></div></div><div class="bedroom-search">2</div></div>
    <div class="mix w-dyn-item" ><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">5200</div></div></div><div class="bedroom-search">1</div></div>
    <div class="mix w-dyn-item"><div class="categ">lettings</div><div><div>price &pound;<div class="price-search">7500</div></div></div><div class="bedroom-search">1</div></div>
</div>

<div class="mixitup-page-list"></div>
<div class="mixitup-page-stats"></div>
