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

$('#prefered-time-date').dateTimePicker({
    dateFormat: "DD/MM/YYYY HH:mm",
    title: "Prefered Time & Date",
    mainClass: "text-field-2 w-input",
    submitBtnClass: "button-violet w-button dpt_modal-button"
});
</script>