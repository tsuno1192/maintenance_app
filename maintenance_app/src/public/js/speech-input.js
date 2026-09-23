(() => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

  if (!SpeechRecognition) {
    document.querySelectorAll('.tmq-speech-btn').forEach((btn) => {
      btn.disabled = true;
      btn.title = 'このブラウザは音声認識に対応していません（Chrome / Edge を推奨）';
    });
    return;
  }

  let activeRecognition = null;
  let activeButton = null;

  function setStatus(fieldName, message, isError = false) {
    const el = document.querySelector(`[data-speech-status-for="${fieldName}"]`);
    if (!el) return;
    if (!message) {
      el.hidden = true;
      el.textContent = '';
      el.classList.remove('is-error');
      return;
    }
    el.hidden = false;
    el.textContent = message;
    el.classList.toggle('is-error', isError);
  }

  function stopActive() {
    if (activeRecognition) {
      activeRecognition.onend = null;
      try {
        activeRecognition.stop();
      } catch (_) {
        // already stopped
      }
      activeRecognition = null;
    }
    if (activeButton) {
      activeButton.classList.remove('is-listening');
      const label = activeButton.querySelector('.tmq-speech-btn__text');
      if (label) label.textContent = '音声入力';
      activeButton = null;
    }
  }

  document.querySelectorAll('.tmq-speech-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const fieldName = btn.dataset.speechTarget;
      const textarea = document.getElementById(`field-${fieldName}`);
      if (!textarea) return;

      if (activeButton === btn) {
        stopActive();
        setStatus(fieldName, '音声入力を停止しました');
        return;
      }

      stopActive();

      const recognition = new SpeechRecognition();
      recognition.lang = 'ja-JP';
      recognition.interimResults = true;
      recognition.continuous = true;

      const baseline = textarea.value.replace(/\s*$/, '');
      let finalTranscript = '';

      recognition.onstart = () => {
        activeRecognition = recognition;
        activeButton = btn;
        btn.classList.add('is-listening');
        const label = btn.querySelector('.tmq-speech-btn__text');
        if (label) label.textContent = '聞いています…';
        setStatus(fieldName, 'マイク入力中（もう一度押すと停止）');
      };

      recognition.onresult = (event) => {
        let interim = '';
        for (let i = event.resultIndex; i < event.results.length; i += 1) {
          const result = event.results[i];
          if (result.isFinal) {
            finalTranscript += result[0].transcript;
          } else {
            interim += result[0].transcript;
          }
        }

        const spoken = finalTranscript + interim;
        const separator = baseline && spoken
          ? (baseline.endsWith('\n') ? '' : '\n')
          : '';
        textarea.value = baseline + separator + spoken;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
      };

      recognition.onerror = (event) => {
        const messages = {
          'not-allowed': 'マイクの使用が許可されていません。ブラウザ設定を確認してください。',
          'no-speech': '音声が検出されませんでした。もう一度お試しください。',
          'audio-capture': 'マイクを検出できませんでした。',
          network: '音声認識サービスへの接続に失敗しました。',
        };
        setStatus(fieldName, messages[event.error] || `音声認識エラー: ${event.error}`, true);
        stopActive();
      };

      recognition.onend = () => {
        if (activeButton === btn) {
          btn.classList.remove('is-listening');
          const label = btn.querySelector('.tmq-speech-btn__text');
          if (label) label.textContent = '音声入力';
          activeButton = null;
          activeRecognition = null;
          setStatus(fieldName, finalTranscript ? '音声入力を反映しました' : '音声入力を終了しました');
        }
      };

      try {
        recognition.start();
      } catch (_) {
        setStatus(fieldName, '音声認識を開始できませんでした。', true);
      }
    });
  });
})();
