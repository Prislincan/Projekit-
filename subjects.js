// Globalni podatki (dostopni na vseh straneh)
window.userSubjects = JSON.parse(localStorage.getItem('userSubjects')) || 
    ["Matematika", "Slovenščina", "Angleščina", "Zgodovina", "Fizika"];

window.userData = JSON.parse(localStorage.getItem('userData')) || { 
    firstName: "Janez", 
    lastName: "Novak" 
};

window.subjectMaterials = {
    "Matematika": ["Algebra.pdf", "Geometrija.docx", "Naloge.zip"],
    "Slovenščina": ["Bralna lista.pdf", "Pravopis.docx"],
    "Angleščina": ["Grammar.pdf", "Vocabulary.docx"],
    "Zgodovina": ["Srednji vek.pdf", "Renesansa.pptx"],
    "Fizika": ["Mehanika.pdf", "Optika.docx"]
};

window.submittedAssignments = JSON.parse(localStorage.getItem('submittedAssignments')) || {};

// Funkcije za delo s predmeti
function loadSubjects() {
    const subjectList = document.getElementById('subjectList');
    if (!subjectList) return;
    
    subjectList.innerHTML = '';
    
    window.userSubjects.forEach(subject => {
        subjectList.innerHTML += `
            <div class="subject-item">
                <h3>${subject}</h3>
                <p>Število gradiv: ${window.subjectMaterials[subject]?.length || 0}</p>
                <div class="subject-actions">
                    <a href="gradiva.html" class="btn btn-outline btn-sm">📖 Ogled gradiv</a>
                </div>
            </div>
        `;
    });
}

function updateSubjectSelect() {
    const selects = document.querySelectorAll('#subjectSelect, #assignmentSubject');
    
    selects.forEach(select => {
        if (!select) return;
        
        select.innerHTML = '<option value="">-- Izberite predmet --</option>';
        window.userSubjects.forEach(subject => {
            select.innerHTML += `<option value="${subject}">${subject}</option>`;
        });
    });
}

function addSubject(newSubject) {
    if (!newSubject.trim()) {
        alert('Prosimo, vnesite ime predmeta!');
        return false;
    }
    
    if (window.userSubjects.includes(newSubject)) {
        alert('Ta predmet že obstaja na vašem seznamu!');
        return false;
    }
    
    window.userSubjects.push(newSubject);
    window.subjectMaterials[newSubject] = [];
    
    // Shrani v localStorage
    localStorage.setItem('userSubjects', JSON.stringify(window.userSubjects));
    
    return true;
}

function removeSubject(subject) {
    window.userSubjects = window.userSubjects.filter(s => s !== subject);
    
    // Shrani v localStorage
    localStorage.setItem('userSubjects', JSON.stringify(window.userSubjects));
    
    return true;
}