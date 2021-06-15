const svalaUrl = 'http://demo.spraakdata.gu.se/arild/svala/'
const dbUrl = 'http://demo.spraakdata.gu.se/arild/larkadb/'
const appUrl = 'http://demo.spraakdata.gu.se/arild/larkadb/app.html'
const essayId = 'Q11QT1'
let graph = null

/** Save essay and open it with Svala */
async function gotoSvala() {
    if (!graph) saveEssay();
    console.log('leaving for Svala');
    window.location = `${svalaUrl}#backurl=${btoa(appUrl)}&backend=${btoa(dbUrl)}&essay=${essayId}&start_mode=anonymization`
}

/** Save essay input to backend. */
const saveEssay = () => postGraph(textToGraph(readText()));

/** Read text input. */
const readText = () => document.getElementById('text').value

/** Post essay graph to backend. */
async function postGraph(graph) {
    const saveResponse = await fetch(`${dbUrl}${essayId}`, {
        method: 'POST',
        body: JSON.stringify(graph),
    })
    console.log('saved', await saveResponse.json());
}

/** Tokenize the way Svala wants it. */
const tokenize = (text) => text.trim().split(/\s|(?=[,\.!?])/)

/** Create graph from text. */
const textToGraph = (text) => tokenize(text)
    .reduce((g, token, i) => {
        g.source.push({ id: `s${i}`, text: `${token} ` })
        g.target.push({ id: `t${i}`, text: `${token} ` })
        g.edges[`e-s${i}-t${i}`] = { id: `e-s${i}-t${i}`, ids: [`s${i}`, `t${i}`], labels: [], manual: false }
        return g;
    }, graphInit())

/** Create a new, empty graph. */
const graphInit = () => ({source: [], target: [], edges: {}})

/** Fetch graph from backend. */
async function fetchGraph() {
    const response = await fetch(`${dbUrl}${essayId}`)
    const data = await response.json()
    console.log('fetched', data);
    if (!data.error) {
        graph = JSON.parse(data.state)
        console.log('graph', graph);
        loadGraph(graph)
    }
}
fetchGraph();

/** Load graph into GUI. */
function loadGraph(graph) {
    document.getElementById('svala').style.display = 'block';
    document.getElementById('text').innerHTML = graph.source.map(token => token.text).join('')
    document.getElementById('anon').innerHTML = graph.target.map(token => token.text).join('')
    document.getElementById('text').setAttribute('disabled', 'disabled');
}

/** Delete remote essay and reload page. */
async function reset() {
    const response = await fetch(`${dbUrl}${essayId}`, {
        method: 'DELETE',
    })
    console.log('deleted', await response.json())
    window.location.reload()
}
