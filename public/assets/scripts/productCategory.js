document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".category-checkbox");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            if (this.checked) {
                let parentLi = this.closest("li").parentElement.closest("li");
                while (parentLi) {
                    let parentCheckbox =
                        parentLi.querySelector(".category-checkbox");
                    if (parentCheckbox && !parentCheckbox.checked) {
                        parentCheckbox.checked = true;

                        $(parentCheckbox).trigger("change");
                    }
                    parentLi = parentLi.parentElement.closest("li");
                }
            } else {
                const childCheckboxes = this.closest("li").querySelectorAll(
                    "ul .category-checkbox",
                );
                childCheckboxes.forEach((child) => {
                    if (child.checked) {
                        child.checked = false;
                        $(child).trigger("change");
                    }
                });
                let parentLi = this.closest("li").parentElement.closest("li");
                while (parentLi) {
                    let parentCheckbox =
                        parentLi.querySelector(".category-checkbox");
                    if (parentCheckbox) {
                        const siblingsChecked = parentLi.querySelectorAll(
                            "ul .category-checkbox:checked",
                        );
                        if (siblingsChecked.length > 0) {
                            parentCheckbox.checked = true;
                            $(parentCheckbox).trigger("change");
                        } else {
                            parentCheckbox.checked = false;
                            $(parentCheckbox).trigger("change");
                        }
                    }
                    parentLi = parentLi.parentElement.closest("li");
                }
            }
        });
    });
});

