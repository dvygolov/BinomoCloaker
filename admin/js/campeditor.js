async function campEditor(action, campId=null, name=null) {
    let body = `action=${action}`;
    if (campId)
        body += `&campId=${campId}`;
    if (name)
        body += `&name=${name}`;

    let curPath = window.location.href;
    if (curPath.endsWith(".php"))
        curPath = curPath.split('/').slice(0, -1).join('/');
    if (!curPath.endsWith("/"))
        curPath += "/";
    curPath+="campeditor.php";

    let res = await fetch(curPath, {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body
    });
    let js = await res.json();
    if (js.error)
        alert(`An error occured: ${js.result}`);
    else
        window.location.reload();
}

async function campActionsHandler(e, cell) {
    let target = e.target;

    // Check if the target is the <i> element, if so, get the parent <button>
    if (target.tagName === 'I') {
        target = target.closest('button');
    }

    const row = cell.getRow();
    const campaignId = row.getData().id;

    if (target.classList.contains('btn-rename')) {
        const newName = prompt("Enter new campaign name:");
        if (newName==null) return;
        if (newName) {
            await campEditor('ren', campaignId, newName);
            return;
        }
        else{
            alert('Campaign name can not be empty!');
            return;
        }
    }

    if (target.classList.contains('btn-delete')) {
        if (confirm(`Are you sure? Going to delete campaign ${row.getData().name}.`)) {
            await campEditor('del', campaignId);
            return;
        }
        return;
    }

    if (target.classList.contains('btn-clone')) {
        await campEditor('dup', campaignId);
        return;
    }

    if (target.classList.contains('btn-stats')) {
        window.location.href = `statistics.php?campId=${campaignId}`;
        return;
    }

    if (target.classList.contains('btn-allowed')) {
        window.location.href = `clicks.php?campId=${campaignId}&filter=allowed`;
        return;
    }

    if (target.classList.contains('btn-blocked')) {
        window.location.href = `clicks.php?campId=${campaignId}&filter=blocked`;
        return;
    }

    if (target.classList.contains('btn-leads')) {
        window.location.href = `clicks.php?campId=${campaignId}&filter=leads`;
        return;
    }
}
