// Voice accessibility: Text-to-speech for notices
function speakNotice(text, lang = 'en') {
    if (!('speechSynthesis' in window)) {
        alert('Sorry, your browser does not support text-to-speech.');
        return;
    }
    let voices = window.speechSynthesis.getVoices();
    let voice = voices.find(v => v.lang.startsWith(lang));
    let utter = new SpeechSynthesisUtterance(text);
    utter.lang = lang === 'gu' ? 'gu-IN' : (lang === 'hi' ? 'hi-IN' : 'en-US');
    if (voice) utter.voice = voice;
    window.speechSynthesis.speak(utter);
}
// Usage: speakNotice('Your notice text', 'hi'); 