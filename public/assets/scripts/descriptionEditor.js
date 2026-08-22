correctULTagFromQuill = (str) => {
    if (str) {
        let re = /(<ol><li data-list="bullet">)(.*?)(<\/ol>)/;
        let strArr = str.split(re);

        while (
            strArr.findIndex((ele) => ele === '<ol><li data-list="bullet">') !==
            -1
        ) {
            let index = strArr.findIndex(
                (ele) => ele === '<ol><li data-list="bullet">',
            );
            if (index) {
                strArr[index] = '<ul><li data-list="bullet">';
                let endTagIndex = strArr.findIndex((ele) => ele === "</ol>");
                strArr[endTagIndex] = "</ul>";
            }
        }
        return strArr.join("");
    }
    return str;
};
const quill = new Quill("#editor", {
    theme: "snow",
    modules: {
        toolbar: [
            [
                {
                    header: [1, 2, 3, 4, 5, 6, false],
                },
            ],
            [
                {
                    font: [],
                },
            ],
            ["bold", "italic", "underline", "strike", "blockquote"],
            [
                {
                    list: "ordered",
                },
                {
                    list: "bullet",
                },
            ],
            [
                {
                    align: [],
                },
            ],
            [
                {
                    script: "sub",
                },
                {
                    script: "super",
                },
            ],
            [
                {
                    indent: "-1",
                },
                {
                    indent: "+1",
                },
            ],
            [
                {
                    direction: "rtl",
                },
            ],
            [
                {
                    color: [],
                },
                {
                    background: [],
                },
            ],
            ["link", "image", "video", "formula"],
        ],
    },
});
quill.on("text-change", function (delta, oldDelta, source) {
    document.getElementById("description").value = correctULTagFromQuill(
        quill.root.innerHTML,
    );
});
