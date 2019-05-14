<link href="https://dl.dropboxusercontent.com/s/5zpp1ebeiyyaqpw/datetimepicker.css" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.0/moment-with-locales.min.js"></script>
<script src="https://dl.dropboxusercontent.com/s/0tsuxegnkxd91qb/datetimepicker.js"></script>
<script>
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

$('#prefered-time-date2').addClass('dtp_main');
$('#prefered-time-date2').dateTimePicker({
    dateFormat: "DD/MM/YYYY HH:mm",
    title: "Prefered Time & Date",
    mainClass: "text-field-2 w-input",
    submitBtnClass: "button-violet w-button dpt_modal-button",
    resultElementId: "prefered_time_date-input2"
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

</script>