<div class="content-container unit size3of4 lastUnit">
    <article>
        <h1>$Title</h1>
        <div class="content">
            <div id="media-form" class="media__form">
                <div class="poster"
                     style="background-image:url('{$getPosterImageByCatalogueSlug.ScaleWidth(350).Link}'), url({$resourceURL('themes/app/images/blank.png')})"
                >
                    <span class="loader"></span>
                </div>
                $Form
                $Message
            </div>
        </div>
    </article>
</div>
