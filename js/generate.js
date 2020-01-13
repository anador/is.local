function loadFile(url, callback) {
    PizZipUtils.getBinaryContent(url, callback);
}
function generate1(time, country, quantity) {
    loadFile("templates/template1.docx", function (error, content) {
        if (error) { throw error };
        var zip = new PizZip(content);
        var doc = new window.docxtemplater().loadZip(zip)
        doc.setData({
            time: time,
            country: country,
            quantity: quantity,
        });
        try {
            // render the document (replace all occurences of {first_name} by John, {last_name} by Doe, ...)
            doc.render()
        }
        catch (error) {
            var e = {
                message: error.message,
                name: error.name,
                stack: error.stack,
                properties: error.properties,
            }
            console.log(JSON.stringify({ error: e }));
            // The error thrown here contains additional information when logged with JSON.stringify (it contains a property object).
            throw error;
        }
        var out = doc.getZip().generate({
            type: "blob",
            mimeType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        }) //Output the document using Data-URI
        saveAs(out, "Отчет 1.docx")
    })
}

function generate2(time, category, timeOfDay, quantity) {
    loadFile("templates/template2.docx", function (error, content) {
        if (error) { throw error };
        var zip = new PizZip(content);
        var doc = new window.docxtemplater().loadZip(zip)
        doc.setData({
            time: time,
            category: category,
            timeOfDay: timeOfDay,
            quantity: quantity,
        });
        try {
            // render the document (replace all occurences of {first_name} by John, {last_name} by Doe, ...)
            doc.render()
        }
        catch (error) {
            var e = {
                message: error.message,
                name: error.name,
                stack: error.stack,
                properties: error.properties,
            }
            console.log(JSON.stringify({ error: e }));
            // The error thrown here contains additional information when logged with JSON.stringify (it contains a property object).
            throw error;
        }
        var out = doc.getZip().generate({
            type: "blob",
            mimeType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        }) //Output the document using Data-URI
        saveAs(out, "Отчет 2.docx")
    })
}
function generate3(time, date, h1, h2, quantity) {
    loadFile("templates/template3.docx", function (error, content) {
        if (error) { throw error };
        var zip = new PizZip(content);
        var doc = new window.docxtemplater().loadZip(zip)
        doc.setData({
            time: time,
            date: date,
            h1: h1,
            h2: h2,
            quantity: quantity,
        });
        try {
            // render the document (replace all occurences of {first_name} by John, {last_name} by Doe, ...)
            doc.render()
        }
        catch (error) {
            var e = {
                message: error.message,
                name: error.name,
                stack: error.stack,
                properties: error.properties,
            }
            console.log(JSON.stringify({ error: e }));
            // The error thrown here contains additional information when logged with JSON.stringify (it contains a property object).
            throw error;
        }
        var out = doc.getZip().generate({
            type: "blob",
            mimeType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        }) //Output the document using Data-URI
        saveAs(out, "Отчет 3.docx")
    })
}