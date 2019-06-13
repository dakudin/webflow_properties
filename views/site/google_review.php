<?php
$this->registerCssFile('https://cdn.rawgit.com/stevenmonson/googleReviews/master/google-places.css', ['position' => $this::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/gh/stevenmonson/googleReviews@6e8f0d794393ec657dab69eb1421f3a60add23ef/google-places.js', [
    'position' => $this::POS_READY,
    'depends' => [\yii\web\YiiAsset::className()]
]);
$this->registerJsFile('https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyDeivU57j-macv2fXXgbhKGM6cqMLmnAFI&signed_in=true&libraries=places', ['position' => $this::POS_HEAD]);

$js = <<< JS
    jQuery(document).ready(function( $ ) {
        $("#google-reviews").googlePlaces({
            placeId: 'ChIJp2QxV_sJVFMR1DEp1x_16F8' //Find placeID @: https://developers.google.com/places/place-id
            , render: ['reviews']
            , min_rating: 4
            , max_rows:4
        });
    });
JS;

$this->registerJS($js, $this::POS_READY);

?>
<div id="google-reviews"></div>

