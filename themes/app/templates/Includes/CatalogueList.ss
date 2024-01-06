<div class="list-media__item">
    <div class="list-media__poster--wrapper">
        <div
                class="list-media__poster"
                style="background-image:url('$Poster.ScaleHeight(350).Link'), url({$resourceURL('themes/app/images/blank.png')})"
        ></div>

        <div class="list-media__metadata">
            <h2 class="list-media__title">
                <a
                        href="{$ParentTop.ProfileURL}title/{$ID}"
                        class="list-media__title"
                        title="Browse {$Title} (Updated {$LastEdited.Ago})"
                >$Title</a>
            </h2>
            <span class="list-media__metadata--source badge">$Source</span>
            <span class="list-media__metadata--quality badge">$Quality</span>
            <span class="list-media__metadata--status {$Status} badge">$Status</span>
            <span title="view comments here" class="comments fa fa-comments fa-2x"
                  data-toggle="modal"
                  data-target="#CommentsModal"
                  data-commentsurl="{$ParentTop.Link}comments/{$ID}"
                  data-catalogueID="{$ID}"
                  data-commentform="{$ParentTop.Link}handleComment/"
            ></span>
            <a class="list-media__metadata--edit" href="{$ParentTop.MaintenanceFormPageLink}edit/{$ID}">
                <span title="edit {$Title}" class="fa fa-wrench fa-2x"></span>
            </a>
            <p class="list-media__metadata--email">
                Updated $LastEdited.Ago by
                <a href="mailto:{$Owner.Email}?subject=Can I get {$Title} off you?<eom>">$Owner.FirstName</a>
            </p>
            <div class="list-media__metadata--view"
                 data-toggle="modal"
                 data-target="#CatalogueModal"
                 data-profileurl="{$ParentTop.ProfileURL}title/{$ID}"
            >
                <button type="button" class="btn btn-default"><span class="fa fa-eye"></span> Quick view</button>
            </div>
            <div class="list-media__metadata--keywords hidden">$keywords</div>
            <div class="list-media__metadata--genres hidden">$genres</div>
        </div>
    </div>
</div>
