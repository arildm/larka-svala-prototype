const express = require('express')
const g = require('./g.json')

const app = express();

function loadTask(req, res) {
    const { project, taskType, key } = req.params
    console.log({project, taskType, key})
    res.json(g)
}

app.get("/:project/:taskType/:key", loadTask);

const port = 8001
app.listen(port, function () {
	console.log(`http://localhost:${port}`);
});
