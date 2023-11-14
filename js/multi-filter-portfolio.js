let pulseMfp = {
        postHeight: 0,
        totalPosts: 0,
        totalPages: 0,
        numberOfItems: 12,
        sectionHeight: 0,
        allowedHeight: 0,
        currentPage: 1,
        perShowMore: 3,
        firstPage: 12,
    },
    subjectClassList = {
        container: ".pulse-mfp-container",
        filterControls: ".pulse-mfp-filter",
        loadBtn: ".pulse-mfp-btn",
        post: ".pulse-mfp-post",
        gallery: ".pulse-mfp-gallery",
        galleryContent: ".pulse-mfp-gallery .content",
        closeGallery: ".pulse-mfp-close-gallery",
    };
jQuery(document).ready(function ($) {
    // Init variables
    let currentPage = 1,
        container = $(subjectClassList.container), // Items container
        filterControls = $(subjectClassList.filterControls), // Filter dropdown
        loadBtn = $(subjectClassList.loadBtn), // Load more button
        closeGalleryBtn = $(subjectClassList.closeGallery), // Closed modal button
        gallery = $(subjectClassList.gallery), // Gallery
        galleryContent = $(subjectClassList.galleryContent); // Gallery content
    // Fetches the height the post element
    pulseMfp.postHeight = $(subjectClassList.post).height();
    $.ajax({
        url: multi_filter_portfolio_ajax_object.ajax_url,
        data: {
            action: "mfp_load_more_posts_json",
            security: multi_filter_portfolio_ajax_object.nonce,
        },
        type: "POST",
        success: function (response) {
            if (response.success) {
                // Fetch all posts
                let posts = response.data.posts;
                // Append the new posts to the container
                pulseMfp.totalPosts = response.data.total_posts;
                // Calculate total pages
                pulseMfp.totalPages = Math.ceil(
                    pulseMfp.totalPosts / pulseMfp.numberOfItems
                );
                // Loop through the posts
                for (let i = 0; i < posts.length; i++) {
                    let post = posts[i];
                    if (post.thumbnail && post.thumbnail !== "") {
                        // Only shows the post that has thumbnail
                        container.append(renderPost(post));
                    } else {
                        console.error(
                            'Post "' +
                                post.title +
                                '" does not have image, not shown'
                        );
                    }
                }
                pulseMfp.postHeight = $(subjectClassList.post).height();
                // Set height of the container
                adjustHeight();
                // Opening the post
                $(subjectClassList.container + " a").click(function () {
                    createGallery("<h2 class='loading'>Loading...</h2>");
                    $(".pulse-mfp-gallery").css("position", "fixed");
                    // initClicker();
                    $.get($(this).attr("href"), function (data) {
                        $(".pulse-mfp-gallery").removeAttr("style");
                        $(".pulse-mfp-gallery .content").html(data);
                        // Initialize opening post for other items loaded in related post section
                        // $(".pulse-mfp-gallery .content a.cbp-singlePage").click(
                        //     function() {
                        //         $(".pulse-mfp-gallery").addClass("fade-out");
                        //         setTimeout(function() {
                        //             createGallery("<h2 class='loading'>Loading...</h2>");
                        //             $.get($(this).attr("href"), function(data) {
                        //                 $(".pulse-mfp-gallery .content").html(data);
                        //             });
                        //         }, 300);
                        //     }
                        // );
                        initSlider();
                    });
                    return false;
                });
                //console.log("Adjust height:", adjustHeight())
            }
        },
    });
    // Enable dropdown styles for filter dropdowns
    filterControls.dropdown({ useLabels: false });
    // Trigger filter on change to filter the items
    filterControls.change(filterItems);
    // Let the main container be empty whe the page has loaded
    container.html("");
    // Allow load more to show pages
    loadBtn.click(showMore);
    closeGalleryBtn.click(function () {
        $(subjectClassList.gallery).fadeOut(400);
    });

    function initClicker() {
        $.get($(this).attr("href"), function (data) {
            $(".pulse-mfp-gallery .content").html(data);
            $(subjectClassList.container + " a, a.cbp-singlePage").click(
                initClicker
            );
            initSlider();
        });
    }

    function openThis() {
        $.get($(this).attr("href"), function (data) {
            $(".pulse-mfp-gallery .content").html(data);
            initSlider();
        });
    }

    function processTaxonomies(tax) {
        let taxes = "";
        Object.keys(tax).forEach(function (key, index) {
            let singleTax = "";
            tax[key].map((val) => {
                singleTax += `${val.replace(/ /g, "-").toLowerCase()} `;
            });
            taxes += singleTax;
        });
        return taxes;
    }

    function filterItems() {
        let filterClassList = [],
            filters = {},
            subjects = $(subjectClassList.post);
        $(subjectClassList.filterControls + " option:selected").each(
            function () {
                let filterValue = $(this).val();
                if (filterValue !== "") {
                    let filterGroup = $(this).parent().data("filter-group");
                    // Create if does not exist
                    if (!filters[filterGroup]) filters[filterGroup] = [];
                    filters[filterGroup].push(
                        subjectClassList.post + filterValue
                    );
                    filterClassList.push(subjectClassList.post + filterValue);
                }
            }
        );
        let groups = Object.values(filters);
        let result = generateCombinations(groups);
        // console.log("Results:", result);
        // console.log("filterClassList:", filterClassList);
        // let classList = filterClassList.join(", ");
        let classList = result.join(", ");

        // If no filters are selected
        if (classList === "")
            // Removes the hide class from all the elements
            return subjects.removeClass("pulse-mfp-hide");
        // If filters are selected, hides the other elements
        $(subjectClassList.post + ":not(" + classList.replace(/pulse-mfp-post a/g, "") + ")").addClass("pulse-mfp-hide");
        // Removes the hide class from any elements that have to be displayed
        $(classList).removeClass("pulse-mfp-hide");
        return true;
    }

    function generateCombinations(
        groups,
        currentIndex = 0,
        currentCombination = ""
    ) {
        if (currentIndex === groups.length) {
            return [currentCombination];
        }

        let result = [];
        for (let item of groups[currentIndex]) {
            result.push(
                ...generateCombinations(
                    groups,
                    currentIndex + 1,
                    currentCombination + item
                )
            );
        }

        return result;
    }

    function filterItemsLegacy() {
        let filterClassList = [],
            subjects = $(subjectClassList.post);
        console.log(subjects);
        $(subjectClassList.filterControls + " option:selected").each(
            function () {
                let filterValue = $(this).val();
                filterClassList.push(subjectClassList.post + filterValue);
            }
        );
        let classList = filterClassList.join(", ");
        // If no filters are selected
        if (classList === "")
            // Removes the hide class from all the elements
            return subjects.removeClass("pulse-mfp-hide");
        // If filters are selected, hides the other elements
        $(subjectClassList.post + ":not(" + classList.replace(/pulse-mfp-post a/g, "") + ")").addClass("pulse-mfp-hide");
        // Removes the hide class from any elements that have to be displayed
        $(classList).removeClass("pulse-mfp-hide");
        return true;
    }

    function adjustHeight() {
        // Auto adjust height
        pulseMfp.sectionHeight =
            (pulseMfp.postHeight + 8) *
            pulseMfp.perShowMore *
            pulseMfp.currentPage;
        $(subjectClassList.container).css("max-height", pulseMfp.sectionHeight + "px");
        return pulseMfp.sectionHeight;
    }

    function renderPost(post) {
        return (
            `<a href="${post.permalink}" class="pulse-mfp-post post ${processTaxonomies(post.taxonomies)}" id="P${post.id}">` +
                `<div class="overlay">` +
                    `<h2>${post.title}</h2>` +
                `</div>` +
                `<div class="image-wrapper pulse-mfp-item-inner">` +
                    //`<div class="image" style="background-image:url('${post.thumbnail_small}')"></div>` +
                    `<img src="${post.thumbnail}" class="pulse-mfp-img" style="object-fit:cover;width:100%;height:100%;" loading="lazy" data-image="${post.thumbnail}" onloaddd="setTimeout(() => { this.src='${post.thumbnail}' }, 1000)"/>` +
                `</div>` +
            `</a>`
        );
    }

    function imageLoaded(e) {
        alert(1);
        console.log("Image loaded", e);
    }

    function createGallery(data) {
        $(".pulse-mfp-gallery").remove();
        $("body").append(renderGallery(data));
    }

    function renderGallery(data) {
        return (
            `<div class="pulse-mfp-gallery">` +
                `<button class="pulse-mfp-close-gallery" onclick="this.parentElement.remove();"></button>` +
                `<div class="content">${data}</div>` +
            `</div>`
        );
    }

    function pulseMfpLoad(e) {
        document.getElementById("pulse-mfp-gallery").style.display = "block";
        alert(e);
    }

    function calculateAllowedHeight() {
        if (subjectClassList) {
            if (!subjectClassList.post) return false;
            pulseMfp.allowedHeight = $(subjectClassList.post).height();
            return pulseMfp.allowedHeight;
        }
    }

    function showMore() {
        pulseMfp.currentPage += 1;
        adjustHeight();
    }

    function initSlider() {
        let slider = document.querySelector(
            ".pulse-mfp-gallery-slider-wrapper"
        );
        const prevBtn = document.querySelector(".pulse-mfp-button-prev");
        const nextBtn = document.querySelector(".pulse-mfp-button-next");
        let slideWidth = document.querySelector(".pulse-mfp-slide").clientWidth;
        let currentIndex = 0;

        function updateSliderPosition() {
            slider = document.querySelector(
                ".pulse-mfp-gallery-slider-wrapper"
            );
            slideWidth = document.querySelector(".pulse-mfp-slide").clientWidth;
            slider.style.transform = `translateX(-${
                currentIndex * slideWidth
            }px)`;
            console.log("Width:", slideWidth);
            console.log(
                "transform:",
                `translateX(-${currentIndex * slideWidth}px)`
            );
        }

        function goToNextSlide() {
            currentIndex = (currentIndex + 1) % slider.childElementCount;
            updateSliderPosition();
        }

        function goToPrevSlide() {
            currentIndex =
                (currentIndex - 1 + slider.childElementCount) %
                slider.childElementCount;
            updateSliderPosition();
        }

        nextBtn.addEventListener("click", goToNextSlide);
        prevBtn.addEventListener("click", goToPrevSlide);
    }
    jQuery(window)
        .resize(function () {
            // Checks if the post elements are there or not
            if ($(subjectClassList.post).length)
                pulseMfp.postHeight = $(subjectClassList.post).height();
            if (typeof adjustHeight === "function") adjustHeight();
        })
        .resize();
});
