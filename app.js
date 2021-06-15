const svalaUrl = 'http://demo.spraakdata.gu.se/arild/svala/'
const dbUrl = 'http://demo.spraakdata.gu.se/arild/larkadb/'
const appUrl = 'http://demo.spraakdata.gu.se/arild/larkadb/app.html'
const essayId = 'Q11QT1'
let graph = null

async function gotoSvala() {
    if (!graph) saveEssay();
    console.log('leaving for Svala');
    window.location = `${svalaUrl}#backurl=${btoa(appUrl)}&backend=${btoa(dbUrl)}&essay=${essayId}&start_mode=anonymization`
}

async function saveEssay() {
    const essayText = document.getElementsById('text')[0].value
    const saveResponse = await fetch(`${dbUrl}${essayId}`, {
        method: 'POST',
        body: JSON.stringify(textToSvala(essayText)),
    })
    console.log('saved', await saveResponse.json());
}

function textToSvala(text) {
    const g = {
        source: [],
        target: [],
        edges: {},
    }
    const tokens = text.trim().split(/\s|(?=[,\.!?])/)
    tokens.forEach((token, i) => {
        g.source.push({ id: `s${i}`, text: `${token} ` })
        g.target.push({ id: `t${i}`, text: `${token} ` })
        g.edges[`e-s${i}-t${i}`] = {
            id: `e-s${i}-t${i}`,
            ids: [`s${i}`, `t${i}`],
            labels: [],
            manual: false,
        }
    })
    return g
}

async function fetchGraph() {
    const response = await fetch(`${dbUrl}${essayId}`)
    const data = await response.json()
    console.log('fetched', data);
    if (!data.error) {
        graph = JSON.parse(data.state)
        console.log('graph', graph);
        document.getElementById('svala').style.display = 'block';
        document.getElementById('text').innerHTML = graph.source.map(token => token.text).join('')
        document.getElementById('anon').innerHTML = graph.target.map(token => token.text).join('')
        document.getElementById('text').setAttribute('disabled', 'disabled');
    }
}
fetchGraph();

async function reset() {
    const response = await fetch(`${dbUrl}${essayId}`, {
        method: 'DELETE',
    })
    console.log('deleted', await response.json())
    window.location.reload()
}
