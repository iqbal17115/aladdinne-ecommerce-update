document.addEventListener("DOMContentLoaded", function () {
    const additionalContainer = document.getElementById("additionalElements");
    const pickerTemplate = document.getElementById("imagePickerTemplate");

    function initializeImagePicker(wrapper) {
        if (!wrapper) return;
        const removeBtn = wrapper.querySelector(".delete");
        const thumbnailPathInput = wrapper.querySelector(".thumbnailPath");
        const imageHolder = wrapper.querySelector(".mainThumbnail img");
        removeBtn?.addEventListener("click", () => removeThumbnail(wrapper));
        new MutationObserver(() => {
            const newValue = thumbnailPathInput.value;
            removeBtn.style.display = newValue ? "block" : "none";

            const allWrappers = [
                ...additionalContainer.querySelectorAll(".thumbnail-wrapper"),
            ];
            if (newValue && allWrappers.at(-1) === wrapper) addThumbnail();
        }).observe(thumbnailPathInput, {
            attributes: true,
            attributeFilter: ["value"],
        });
        wrapper.setUrlCallback = function (items) {
            if (!items?.length) return;

            const imageUrl = items[0].url;
            thumbnailPathInput.value = imageUrl.replace(
                window.location.origin + "/storage/",
                "",
            );
            imageHolder.src = imageUrl;

            thumbnailPathInput.dispatchEvent(
                new Event("input", {
                    bubbles: true,
                }),
            );
        };
        initializeLfmForElement(wrapper);
    }

    function initializeLfmForElement(container) {
        container.querySelectorAll(".lfm").forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const iframe = document.getElementById("lfmIframe");
                iframe.dataset.containerId = button.dataset.containerId;
                iframe.src = `${route_prefix}?type=file&callback=SetUrl`;
                $("#lfmModal").modal("show");
            });
        });
    }

    function addThumbnail() {
        if (!pickerTemplate) return;
        const clone = pickerTemplate.content.cloneNode(true);
        const newWrapper = clone.querySelector(".thumbnail-wrapper");
        const container = newWrapper.querySelector(".image-container");
        container.dataset.containerId = `picker-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        additionalContainer.appendChild(clone);
        initializeImagePicker(additionalContainer.lastElementChild);
    }

    function removeThumbnail(wrapper) {
        const allWrappers =
            additionalContainer.querySelectorAll(".thumbnail-wrapper");
        if (allWrappers.length > 1) {
            wrapper.remove();
        } else {
            const img = wrapper.querySelector(".mainThumbnail img");
            const input = wrapper.querySelector(".thumbnailPath");
            img.src = input.dataset.defaultImage;
            input.value = "";
        }
    }
    additionalContainer
        .querySelectorAll(".thumbnail-wrapper")
        .forEach(initializeImagePicker);
    window.SetUrl = function (items) {
        const iframe = document.getElementById("lfmIframe");
        if (!iframe?.dataset.containerId) return;
        const container = document.querySelector(
            `.image-container[data-container-id="${iframe.dataset.containerId}"]`,
        );
        const wrapper = container?.closest(".thumbnail-wrapper");
        wrapper?.setUrlCallback?.(items);
    };
});
