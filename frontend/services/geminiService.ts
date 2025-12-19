import { GoogleGenAI, Type } from '@google/genai';
import { TajwidFeedback } from '../types';

const blobToBase64 = (blob: Blob): Promise<string> =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(blob);
    reader.onload = () => {
      // remove the `data:...;base64,` prefix
      const base64String = (reader.result as string).split(',')[1];
      resolve(base64String);
    };
    reader.onerror = (error) => reject(error);
  });


export const askUstadzAI = async (prompt: string): Promise<string> => {
  console.log("Asking Ustadz AI:", prompt);
  try {
    const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });
    const response = await ai.models.generateContent({
        model: 'gemini-2.5-flash',
        contents: prompt,
        config: {
            systemInstruction: "Anda adalah Ustadz AI, seorang asisten virtual yang berpengetahuan luas dalam studi Islam. Jawablah pertanyaan dengan jelas, ringkas, dan berdasarkan dalil dari Al-Qur'an dan Sunnah sesuai pemahaman salaful ummah. Jaga agar jawaban tetap ramah dan mudah dipahami."
        }
    });
    return response.text;
  } catch (error) {
    console.error("Gemini API error in askUstadzAI:", error);
    return "Afwan, terjadi gangguan saat mencoba menghubungi layanan AI. Silakan coba lagi nanti.";
  }
};


export const checkTajwid = async (audioBlob: Blob): Promise<TajwidFeedback> => {
    console.log("Checking Tajwid with AI for audio blob:", audioBlob);
    try {
        const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });
        const base64Audio = await blobToBase64(audioBlob);

        const audioPart = {
            inlineData: {
                mimeType: audioBlob.type || 'audio/wav',
                data: base64Audio,
            },
        };
        
        const textPart = {
            text: "Analisis bacaan Al-Qur'an ini. Berikan umpan balik tentang hukum tajwid dan makhraj huruf. Berikan skor keseluruhan dari 100. Pastikan respons dalam format JSON yang valid.",
        };

        const response = await ai.models.generateContent({
            model: 'gemini-2.5-pro',
            contents: { parts: [audioPart, textPart] },
            config: {
              responseMimeType: "application/json",
              responseSchema: {
                type: Type.OBJECT,
                properties: {
                  overallScore: { type: Type.NUMBER },
                  feedback: {
                    type: Type.ARRAY,
                    items: {
                      type: Type.OBJECT,
                      properties: {
                        type: { type: Type.STRING, enum: ['error', 'info'] },
                        rule: { type: Type.STRING },
                        comment: { type: Type.STRING },
                      }
                    }
                  }
                }
              },
            },
        });
        
        const jsonText = response.text.trim();
        const parsedJson = JSON.parse(jsonText);
        
        // Basic validation
        if (typeof parsedJson.overallScore !== 'number' || !Array.isArray(parsedJson.feedback)) {
            throw new Error("Invalid JSON structure received from API.");
        }

        return parsedJson as TajwidFeedback;

    } catch (error) {
        console.error("Gemini API error in checkTajwid:", error);
        // Return a mock error feedback structure
         return {
            overallScore: 0,
            feedback: [
                {
                    type: 'error',
                    rule: 'Analisis Gagal',
                    comment: 'Maaf, terjadi kesalahan saat menganalisis audio Anda. Silakan coba rekam kembali dengan suara yang lebih jelas.'
                }
            ]
        };
    }
};
