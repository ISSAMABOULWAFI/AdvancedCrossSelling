<div class="row"
     style="background-image: url('{$background}');background-repeat: no-repeat;background-position: center;">
  <h3>{$message}</h3>
  {foreach $results as $result}
    <div class="col-sm-6 col-md-4">
      <div class="thumbnail">
        <a href="{$result.url_product}"><img src="{$result.url_image}" class="img-responsive"></a>
        <div class="caption">
          <h3 class="st-product-header">{$result.name}</h3>
          <div class="st-price">
            <div class="pricetag">
              <div class="price">
                {round($result.price)} Dhs
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  {/foreach}
</div>
</br>