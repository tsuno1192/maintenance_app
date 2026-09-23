(() => {
  const panel = document.getElementById('voice-panel');
  if (!panel) return;

  const endpoint = panel.dataset.voiceEndpoint;
  const csrf = panel.dataset.csrf;
  const transcriptEl = document.getElementById('voice-transcript');
  const statusEl = document.getElementById('voice-status');
  const unsupportedEl = document.getElementById('voice-unsupported');
  const commandBtn = document.getElementById('voice-command-btn');
  const fillBtn = document.getElementById('voice-fill-btn');
  const titleInput = document.getElementById('title');
  const form = document.getElementById('task-create-form');

  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

  if (!SpeechRecognition) {
    unsupportedEl?.classList.remove('hidden');
    commandBtn?.setAttribute('disabled', 'disabled');
    fillBtn?.setAttribute('disabled', 'disabled');
    setStatus('非対応ブラウザです');
    return;
  }

  let recognition = null;
  let activeMode = null;

  function setStatus(text) {
    if (statusEl) statusEl.textContent = text;
  }

  function setTranscript(text) {
    if (transcriptEl) transcriptEl.textContent = text || '—';
  }

  function setListeningUi(isListening, mode) {
    const buttons = [commandBtn, fillBtn];
    buttons.forEach((btn) => {
      if (!btn) return;
      const label = btn.querySelector('[data-label]');
      if (isListening && btn.dataset.mode === mode) {
        btn.classList.add('ring-2', 'ring-offset-2', 'ring-teal-500');
        if (label) label.textContent = '聞き取り中…（もう一度押して停止）';
      } else {
        btn.classList.remove('ring-2', 'ring-offset-2', 'ring-teal-500');
        if (label) {
          label.textContent = btn === commandBtn ? '音声コマンド' : 'フォームへ音声入力';
        }
      }
    });
  }

  function createRecognition(mode) {
    const instance = new SpeechRecognition();
    instance.lang = 'ja-JP';
    instance.interimResults = true;
    instance.continuous = false;
    instance.maxAlternatives = 1;

    instance.onstart = () => {
      activeMode = mode;
      setListeningUi(true, mode);
      setStatus(mode === 'command' ? 'コマンドを話してください' : 'タスク名を話してください');
    };

    instance.onerror = (event) => {
      const map = {
        'not-allowed': 'マイク許可が必要です',
        'no-speech': '音声が検出できませんでした',
        aborted: '音声入力をキャンセルしました',
        network: '音声認識ネットワークエラーです',
      };
      setStatus(map[event.error] || `エラー: ${event.error}`);
      activeMode = null;
      setListeningUi(false, null);
    };

    instance.onend = () => {
      activeMode = null;
      setListeningUi(false, null);
    };

    instance.onresult = async (event) => {
      let interim = '';
      let finalText = '';

      for (let i = event.resultIndex; i < event.results.length; i += 1) {
        const result = event.results[i];
        const text = result[0].transcript.trim();
        if (result.isFinal) finalText += text;
        else interim += text;
      }

      const display = finalText || interim;
      setTranscript(display);

      if (!finalText) return;

      if (mode === 'fill') {
        await handleFill(finalText);
      } else {
        await handleCommand(finalText);
      }
    };

    return instance;
  }

  async function toggleListen(mode) {
    if (recognition && activeMode === mode) {
      recognition.stop();
      recognition = null;
      setStatus('停止しました');
      return;
    }

    if (recognition) {
      recognition.stop();
      recognition = null;
    }

    recognition = createRecognition(mode);
    recognition.start();
  }

  async function handleFill(transcript) {
    setStatus('フォームへ反映中…');
    try {
      const data = await postVoice(transcript, 'fill');
      const title = data?.parsed?.title || transcript;
      if (titleInput) {
        titleInput.value = title;
        titleInput.focus();
      }
      setStatus('入力欄に反映しました。内容を確認して追加できます。');
    } catch (error) {
      if (titleInput) titleInput.value = transcript;
      setStatus('サーバー解析に失敗したため、認識結果をそのまま入力しました。');
    }
  }

  async function handleCommand(transcript) {
    setStatus('コマンド実行中…');
    try {
      const data = await postVoice(transcript, 'command');
      setStatus(data.message || '完了しました');
      // 作成・完了後は一覧を更新
      window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      setStatus(error.message || 'コマンド実行に失敗しました');
    }
  }

  async function postVoice(transcript, mode) {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ transcript, mode }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data.message || `HTTP ${response.status}`);
    }
    return data;
  }

  if (commandBtn) {
    commandBtn.dataset.mode = 'command';
    commandBtn.addEventListener('click', () => toggleListen('command'));
  }
  if (fillBtn) {
    fillBtn.dataset.mode = 'fill';
    fillBtn.addEventListener('click', () => toggleListen('fill'));
  }

  // Enter 連打防止の軽いガード
  form?.addEventListener('submit', () => {
    const submit = form.querySelector('button[type="submit"]');
    if (submit) submit.setAttribute('disabled', 'disabled');
  });
})();
